<?php

/**
 * @file
 * Push Notification Admin Send - sends push notification(s) to user(s)
 *
 */

$default = '{
	"aps": {
		"alert": "My alert message...",
		"badge": 42,
		"sound": "default"
	},
	"customNamespace": {
		"field": "value",
		"anArray": [1,2,3],
		"aNumber": 4
	}
}';

$error = array();
$log = array();

if ($_POST) {

	if (isset($_POST['certificate']) && trim($_POST['certificate']) != '') {
		$cert = $_POST['certificate'];
		if (substr($cert, 0, 4) == 'prod') {
			$server = 'ssl://gateway.push.apple.com:2195';
		} else {
			$server = 'ssl://gateway.sandbox.push.apple.com:2195';
		}
	}
	if (isset($_POST['apnsId']) && trim($_POST['apnsId']) != '') {
		$apnsId = $_POST['apnsId'];
	}
	if (isset($_POST['body']) && trim($_POST['body']) != '') {
		$body = $_POST['body'];
	}
	if (isset($_POST['passphrase']) && trim($_POST['passphrase']) != '') {
		$passphrase = $_POST['passphrase'];
	}

	if (isset($cert) && isset($apnsId) && isset($body)) {

		set_time_limit(0); //Keep process running forever...
		
		date_default_timezone_set('America/New_York');
		
		$ctx = stream_context_create();
		
		stream_context_set_option($ctx, 'ssl', 'local_cert', $cert);
		//stream_context_set_option($ctx, 'ssl', 'passphrase', '4rtsQu3st2o12'); //2013 has no password...
		
		$apnsConnection = stream_socket_client($server, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
		
		if( $apnsConnection ) {
		
			if( $errstr ) {
		
				$log[] = date('Y-m-d H:i')." - Error connecting to APNS: ".$errstr; //log, that we are successfully connected - used for debugging
		
			} else {
		
				$log[] = date('Y-m-d H:i')." - Successfully connected to APNS: ".$server; //log, that we are successfully connected - used for debugging
	
				// get the push tokens - either all or only for those subscribed ppl who are members
				$recipients = array($apnsId);
	
				// for each push token from the db
				foreach ($recipients as $deviceToken) {
					
					$bodyJson = json_decode(stripslashes($body));
					$payload = json_encode($bodyJson);
	
					$log[] = date('Y-m-d H:i')." - Payload: ".$payload;
	
					$msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n",strlen($payload)) . $payload;
					$log[] = date('Y-m-d H:i')." - Pushing message to APNS for token: ".$deviceToken; //another log entry
	
					$result = fwrite($apnsConnection, $msg); //this pushes the message to APNS
	
					if( !$result ) {
	
						$log[] = date('Y-m-d H:i')." - Failure writing to APNS, pipe broken!";
	
						fclose($apnsConnection);
						$log[] = date('Y-m-d H:i')." - Closing existing connection to APNS";
	
						//re-open APNS connection
						$ctx = stream_context_create();
						stream_context_set_option($ctx, 'ssl', 'local_cert', $cert);
						if(isset($passphrase)) {
							stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
						}
	
						$log[] = date('Y-m-d H:i')." - Reconnecting to APNS";
						$apnsConnection = stream_socket_client($server, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
	
						$log[] = date('Y-m-d H:i')." - Bad token: ".$deviceToken;
	
					}
	
				}
		
				$return = "Push was Successful!";
		
				//close APNS connection
				fclose($apnsConnection);
				$log[] = date('Y-m-d H:i')." - Closing connection to APNS";
		
			}
			
		} else {
			$log[] = date('Y-m-d H:i')." - Error connecting to APNS: ".$errstr;
		}
		
		$return = join("<br/>",$log);
		
	}
	
} 

?>

<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Push Notification Tester</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
		
		<?php if(isset($return)) {
			echo "<h1>Server send log:</h1>";
			echo "<p>$return</p>";
		} ?>
		<h1>Send Message</h1>
		<form method="post" action="/">
			<p><label for="certificate">Certificate</label><select id="certificate" name="certificate">
				<optgroup label="Production Certs (Ad Hoc/App Store Builds)">
					<?php
						$dir = "prod-certificates";
						$dh  = opendir($dir);
						while (false !== ($filename = readdir($dh))) {
						    $files[] = $filename;
						}
						sort($files);
						foreach ($files as $file) {
							if ($file != '.' && $file != '..' && pathinfo($file)['extension'] != 'md' ) {
					?>
					<option value="prod-certificates/<?php echo $file ?>" <?php if(isset($cert) && $cert == "prod-certificates/$file") echo 'selected'; ?>><?php echo $file ?></option>
					<?php } } ?>
				</optgroup>
				<optgroup label="Sandbox Certs (Xcode Builds)">
					<?php
						$dir = "sandbox-certificates";
						$dh  = opendir($dir);
						while (false !== ($filename = readdir($dh))) {
						    $files2[] = $filename;
						}
						sort($files2);
						foreach ($files2 as $file) {
							if ($file != '.' && $file != '..' && pathinfo($file)['extension'] != 'md') {
					?>
					<option value="sandbox-certificates/<?php echo $file ?>" <?php if(isset($cert) && $cert == "sandbox-certificates/$file") echo 'selected'; ?>><?php echo $file ?></option>
					<?php } } ?>
				</optgroup>
			</select></p>
			<p><label for="passphrase">Certificate Passphrase</label><input type="text" id="passphrase" name="passphrase" size="100" value="<?php if(isset($passphrase)) echo $passphrase; ?>" placeholder="(Only needed if set on the certificate.)"/></p>
			<p><label for="apnsId">APNS ID</label><input type="text" id="apnsId" name="apnsId" size="100" value="<?php if(isset($apnsId)) echo $apnsId; ?>"/></p>
			<p><label for="body">APNS JSON</label> (See <a href="https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/ApplePushService.html#//apple_ref/doc/uid/TP40008194-CH100-SW9">Apple's Docs</a> for format details)<br/>
				<textarea id="body" name="body" rows="20" cols="80"><?php if(isset($body)) { echo stripslashes($body); } else { echo $default; }?></textarea></p>
			<p><input type="submit" value="Send" />
		</form>
		<h2>How to generate a production certificate:</h2>
		<ol>
			<li>Export both the cert and the private key as a .p12 file</li>
			<li>Generate the PEM on the command line: <code>openssl pkcs12 -in cert.p12 -out apple_push_notification_production.pem -nodes</code></li>
			<li>Verify it works on the command line: <code>openssl s_client -connect gateway.push.apple.com:2195 -cert apple_push_notification_production.pem -debug -showcerts</code></li>
			<li>Place the PEM file in the prod-certificates directory of this application.</li>
		</ol>
		<h2>How to generate a sandbox certificate:</h2>
		<ol>
			<li>Export both the cert and the private key as a .p12 file</li>
			<li>Generate the PEM on the command line: <code>openssl pkcs12 -in cert.p12 -out apple_push_notification_sandbox.pem -nodes</code></li>
			<li>Verify it works on the command line: <code>openssl s_client -connect gateway.sandbox.push.apple.com:2195 -cert apple_push_notification_sandbox.pem -debug -showcerts</code></li>
			<li>Place the PEM file in the sandbox-certificates directory of this application.</li>
		</ol>
		<p>Additional help: <a href="http://developer.apple.com/library/ios/#technotes/tn2265/_index.html">http://developer.apple.com/library/ios/#technotes/tn2265/_index.html</a></p>
    </body>
</html>
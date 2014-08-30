php-apns-pusher
===============

Simple PHP page to send Push Notifications to iOS apps using Apple's APNS service

## How to use php-apns-pusher

1. Clone this repo into a directory on your web server
2. Upload your certificates into the `prod-certificates` and `sandbox-certificates` directories.  (See the README files in those directories for details on how to generate these certificates.)
3. Point your browser at the php-apns-pusher directory on your server.
4. Select the certificate from the drop-down
5. If your certificate has a passphrase, enter it in the Certificate Passphrase box.
6. Enter the APNS ID from your app. (It must be running on a device, this will not work in the simulator.)
7. Alter the APNS JSON payload as necessary.
8. Press the "Send" button.

If everything works, the logs should print at the top of the screen.

The values you enter in the text boxes will be persisted through each submission for your convenience, but are not saved.

## Security Notes

This code is _NOT_ production ready.  In fact it should not even be placed on a publicly-accessible web server without adding some level of security. (Like SSL encryption and a Basic Auth password at the bare minimum.)  I use this project to support iOS app development while my server-side development team is building the real back-end.  Install this locally on [MAMP](http://www.mamp.info/en/), [WAMP](http://www.wampserver.com/en/), the local web server on your Mac (even better with [VirtualHostX](https://clickontyler.com/virtualhostx/ VirtualHostX)), or an intranet test server, but please _DON'T_ put it on the public web.

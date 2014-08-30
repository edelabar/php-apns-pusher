## Place your APNS Production Certificate(s) in this directory.
To Generate a production certificate, follow these steps:

1. Export both the cert and the private key as a .p12 file
2. Generate the PEM on the command line: `openssl pkcs12 -in cert.p12 -out apple_push_notification_production.pem -nodes`
3. Verify it works on the command line: `openssl s_client -connect gateway.push.apple.com:2195 -cert apple_push_notification_production.pem -debug -showcerts`
4. Place the PEM file in this directory.

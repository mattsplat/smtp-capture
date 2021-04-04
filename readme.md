# SMTP Capture

### To run examples
In one terminal 
```
cd example
php serve.php
```
This should start listening on port 1028

In another terminal
```
cd example
php send.php
```
This should send the data to the port. Then an html file will be generated with the rendered email.

# Usage
```
$callback = function (\App\MailRequest $request) {
   // Your logic here
};

$server = IoServer::factory(
    new \App\SMTPConnection($callback, true),
    1028 // use a port over 1024
);

$server->run();

```
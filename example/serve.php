<?php

use MattSplat\SmtpCapture\MailRequest;
use MattSplat\SmtpCapture\SMTPConnection;
use Ratchet\Server\IoServer;

require __DIR__ . '/../vendor/autoload.php';

$callback = function (MailRequest $request) {
    $filename = md5(random_bytes(10)) . '.html';
    $content = $request->html !== ""? $request->html : $request->body;
    file_put_contents($filename, $content);
};

$server = IoServer::factory(
    new SMTPConnection($callback, true),
    1028
);

$server->run();

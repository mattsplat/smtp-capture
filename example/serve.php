<?php

use MattSplat\SmtpCapture\MailRequest;
use MattSplat\SmtpCapture\SMTPConnection;
use Ratchet\Server\IoServer;

require __DIR__ . '/../vendor/autoload.php';

$callback = function (MailRequest $request) {
    $filename = md5(random_bytes(10)) . '.html';
    file_put_contents($filename, var_export([
        'to'=>$request->to,
        'from' => $request->from,
        'subject' => $request->subject,
        'cc' => $request->cc,
    ], 1));
};

$server = IoServer::factory(
    new SMTPConnection($callback, true),
    1028
);

$server->run();

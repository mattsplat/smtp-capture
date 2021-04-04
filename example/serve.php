<?php

use Ratchet\Server\IoServer;

require __DIR__ . '/../vendor/autoload.php';

$callback = function (\App\MailRequest $request) {
    $filename = md5(random_bytes(10)) . '.html';
    file_put_contents($filename, $request->html);
};

$server = IoServer::factory(
    new \App\SMTPConnection($callback, true),
    1028
);

$server->run();
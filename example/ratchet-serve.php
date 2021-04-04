<?php

use Ratchet\Server\IoServer;

require __DIR__ . '/../vendor/autoload.php';

$callback = function(\App\MailRequest $request) {
  echo $request->raw;
};
$server = IoServer::factory(
    new \App\SMTPConnection($callback),
    1028
);

$server->run();

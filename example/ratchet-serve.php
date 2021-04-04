<?php

use Ratchet\Server\IoServer;

require __DIR__ . '/../vendor/autoload.php';

$callback = function(\App\MailRequest $request) {
  file_put_contents(md5(random_bytes(10)).'.html', $request->html);
};

$server = IoServer::factory(
    new \App\SMTPConnection($callback),
    1028
);

$server->run();

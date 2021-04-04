<?php
require_once __DIR__ . '/../vendor/autoload.php';


// Create the Transport
$transport = (new Swift_SmtpTransport('localhost', 1028))
    ->setUsername('username')
    ->setPassword('password');

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);
$html = file_get_contents(__DIR__.'/template.html');
$html = str_replace('<!-- CONTENT AREA -->', 'Here is the message itself', $html);


// Create a message
$message = (new Swift_Message('Wonderful Subject'))
    ->setFrom(['john@doe.com' => 'John Doe'])
    ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])
    ->setBody($html);

// Send the message
$result = $mailer->send($message);

echo "$result \n";
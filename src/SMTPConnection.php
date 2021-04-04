<?php


namespace App;


use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SMTPConnection implements MessageComponentInterface
{
    protected $clients;
    protected $mail_requests = [];
    protected $onComplete;

    const EHLO = "EHLO";
    const HELO = "HELO";
    const RCPT = "RCPT";
    const DATA = "DATA";
    const MAIL = "MAIL";
    const QUIT = "QUIT";
    const STARTTLS = "STARTTLS";

    public function __construct(?\Closure $onComplete = null)
    {
        $this->clients = new \SplObjectStorage;
        $this->onComplete = $onComplete;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $this->mail_requests[$conn->resourceId] = new MailRequest();

        echo "New connection! ({$conn->resourceId})\n";
        $conn->send("220 localhost ESMTP Postfix\r\n");
    }

    public function onMessage(ConnectionInterface $connection, $data)
    {
        $this->mail_requests[$connection->resourceId]->appendRaw($data);

        file_put_contents('./email.log', $data . "\n---------------\n", FILE_APPEND);

        $line_end = "\r\n";
        $terminate_by = '.';
        $response = null;
        $prefix = strtok(trim($data), " ");//substr(trim($data), 0, 4);

        switch ($prefix) {
            case self::STARTTLS:
                $response = "220 UGFzc3dvcmQ6" . $line_end;
                break;
            case self::EHLO:
            case self::HELO:
            case self::RCPT:
            case self::MAIL:
                $response = "250 ok" . $line_end;
                break;
            case self::DATA:
                $response = "354 End data with <CR><LF>.<CR><LF>" . $line_end;
                break;
            case self::QUIT:
                $response = "221 Bye Felicia" . $line_end;
                break;
            case $terminate_by:
                // parse data
                $response = "250 2.6.0 Message accepted" . $line_end . $line_end;
                break;
            default:
                // swift mailer sends a tag along with delimimter
                // e.g. --_=_swift_1617478787_54cb3f7e79702b49d3e4401bcb8bdfff_=_--
                $stripped = trim(preg_replace(['/--_=[\w\d_]*=_--/', "/\n/"], '', trim($data)));
                $last_line = array_filter(explode("\n", $data));
                $last_line = trim($last_line[array_key_last($last_line)]);
                if ($stripped === '.' || $last_line === '.') {
                    $response = "250 2.6.0 Message accepted" . $line_end . $line_end;
                } else {
                    $this->mail_requests[$connection->resourceId]->appendData($data);
                }
        }

        // Send data to client
//        echo "prefix $prefix###\n";
//        echo $data. "\n";
//        echo "###\n";
        if ($response) {
            echo "sending response : $response";
            $connection->send($response);
            echo "###\n";
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        if (isset($this->mail_requests[$conn->resourceId])) {
            if ($this->onComplete !== null && is_callable($this->onComplete)) {
                ($this->onComplete)($this->mail_requests[$conn->resourceId]);
            }
            unset($this->mail_requests[$conn->resourceId]);
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        if (isset($this->mail_requests[$conn->resourceId])) {
            unset($this->mail_requests[$conn->resourceId]);
        }
        $conn->close();
    }

    private function addAttributeLine(string $line, MailRequest $mail_request)
    {
        $prefix = substr($line, 0, 4);
        preg_match('/\<([\w\d@.\-\*]+)>/', $line, $matches);
        if ($prefix === self::RCPT && isset($matches[1])) {
            $mail_request->to[] = $matches[1];
        } elseif ($prefix === self::MAIL) {
            $mail_request->from = $matches[1] ?? "";
        }
    }
}
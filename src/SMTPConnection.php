<?php


namespace MattSplat\SmtpCapture;


use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SMTPConnection implements MessageComponentInterface
{
    protected $clients;
    protected $mail_requests = [];
    protected $onComplete;
    protected $logData;

    const EHLO = "EHLO";
    const HELO = "HELO";
    const RCPT = "RCPT";
    const DATA = "DATA";
    const MAIL = "MAIL";
    const QUIT = "QUIT";
    const STARTTLS = "STARTTLS";

    public function __construct(?\Closure $onComplete = null, bool $logData = false)
    {
        $this->clients = new \SplObjectStorage;
        $this->onComplete = $onComplete;
        $this->logData = $logData;
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

        if($this->logData) {
            file_put_contents('./email.log', $data . "\n", FILE_APPEND);
        }

        $line_end = "\r\n";
        $terminate_by = '.';
        $response = null;
        $prefix = strtok(trim($data), " ");

        switch ($prefix) {
            case self::STARTTLS:
                $response = "220 UGFzc3dvcmQ6" . $line_end;
                break;
            case self::EHLO:
            case self::HELO:
            case self::RCPT:
            case self::MAIL:
                $response = "250 ok" . $line_end;
                $this->mail_requests[$connection->resourceId]->appendRaw($data);
            break;
            case self::DATA:
                $response = "354 End data with <CR><LF>$terminate_by<CR><LF>" . $line_end;
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
                if ($stripped === $terminate_by || $last_line === $terminate_by) {
                    $response = "250 2.6.0 Message accepted" . $line_end . $line_end;
                }
                $this->mail_requests[$connection->resourceId]->appendRaw($data);

        }

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
                $this->mail_requests[$conn->resourceId]->parse();
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


}
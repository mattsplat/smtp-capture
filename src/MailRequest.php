<?php


namespace App;


class MailRequest
{
    public $from = "";
    public $from_name = "";
    public $to = [];
    public $bcc = [];
    public $cc = [];

    public $body = "";
    public $subject = "";

    public $data = "";
    public $raw = "";
    public $html = "";

    public function __construct()
    {
    }

    public function parse() : MailRequest
    {
        $parser = new \PhpMimeMailParser\Parser();
        $parser->setText($this->raw);
        $this->html = $parser->getMessageBody('html');

        $this->to = $parser->getAddresses('to');
        $this->to = $parser->getAddresses('from');

        $this->subject =  $parser->getSubject();
        return $this;
    }

    public function appendData(string $line) : MailRequest
    {
        $this->data .= $line;
        return $this;
    }

    public function appendRaw(string $line) : MailRequest
    {
        $this->raw .= $line;
        return $this;
    }


}
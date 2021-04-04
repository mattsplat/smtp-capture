<?php


namespace MattSplat\SmtpCapture;


use PhpMimeMailParser\Parser;

class MailRequest
{
    public $from = "";
    public $to = [];
    public $bcc = [];
    public $cc = [];

    public $body = "";
    public $subject = "";

    public $raw = "";
    public $html = "";

    public function __construct()
    {
    }

    public function parse() : MailRequest
    {
        $parser = new Parser();
        $parser->setText($this->raw);
        $this->html = $parser->getMessageBody('html');
        $this->body = $parser->getMessageBody('text');

        $this->to = $parser->getAddresses('to');
        $this->to = $parser->getAddresses('from');

        $this->subject =  $parser->getHeader('subject');
        return $this;
    }


    public function appendRaw(string $line) : MailRequest
    {
        $this->raw .= $line;
        return $this;
    }

    public function getContent()
    {
        return $this->html !== "" ? $this->html : $this->body;
    }


}
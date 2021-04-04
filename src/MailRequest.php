<?php


namespace App;


class MailRequest
{
    public $from = "";
    public $to = [];
    public $bcc = [];
    public $cc = [];

    public $body = "";
    public $subject = "";

    public $data = "";
    public $raw = "";

    public function __construct()
    {
    }

    public function parseData() : MailRequest
    {

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
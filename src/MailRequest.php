<?php


namespace MattSplat\SmtpCapture;


use PhpMimeMailParser\Parser;

class MailRequest
{

    /**
     * @var array
     * [
        0 =>
            [
                'display' => 'John Doe',
                'address' => 'john@doe.com',
                'is_group' => false,
            ],
        ],
     */
    public $from = [];

    /**
     * @var array
     */
    public $to = [];

    /**
     * @var array
     */
    public $cc = [];

    /**
     * @var string
     */
    public $body = "";

    /**
     * @var string
     */
    public $subject = "";

    /**
     * @var string
     */
    public $raw = "";

    /**
     * @var string
     */
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
        $this->from = $parser->getAddresses('from');
        $this->cc = $parser->getAddresses('cc');

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
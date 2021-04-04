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
        $start_html_tag = "<!DOCTYPE";
        $parts = explode($start_html_tag, $this->raw);
        $this->html = $start_html_tag. $parts[1];
        $this->html = preg_replace('/=\n/', '', $this->html);
        $this->html = preg_replace('/=3D/', '=', $this->html);
        $this->html = preg_replace('/\.\.(\w)/', '.$1', $this->html);

        foreach(explode('\n', $parts[0] ) as $line) {
            $this->addAttributeLine($line);
        }
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

    private function addAttributeLine(string $line)
    {

        $prefix = strtok(trim($line), ":");
        $value = explode(': ', $line)[1] ?? "";

        switch($prefix) {
            case 'Subject':
                $this->subject = $value;
                break;
            case "From":
                preg_match('/\<([\w\d@.\-\*]+)>/', $value, $matches);
                $this->from[] = $matches[1] ?? "";
                $this->from_name = strtok($value, ' ');
                break;
            case "To":
                $this->to[] = $value;
                break;
            default:
        }

    }

}
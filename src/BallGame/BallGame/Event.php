<?php
namespace BallGame;

class Event {
    public $name;
    public $senderTopic;
    public $recieverTopic;
    public $content;
    public $secret;

    public function __construct($name, $senderTopic, $recieverTopic, $content, $secret)
    {
        $this->name = $name;
        $this->senderTopic = $senderTopic;
        $this->recieverTopic = $recieverTopic;
        $this->content = $content;
        $this->secret = $secret;
    }
}

<?php

require dirname(__DIR__) . '/../../vendor/autoload.php';

use React\EventLoop\Factory;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;
use Thruway\ClientSession;

class Timer
{
    private $gamePrivateTopic;
    private $session;
    private $timer = 0;

    public function __construct($topic)
    {

        $this->gamePrivateTopic = $topic;
        echo "$this->gamePrivateTopic\n";
        $loop = Factory::create();
        $client = new Client('global', $loop);
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080"));
        $client->on('open', function(ClientSession $session) {
            echo "Client opened!\n";
            $this->session = $session;
            $session->subscribe('global', function($args) {
            });
            $session->publish('global', ['dupsko']);
        });

        $loop->addPeriodicTimer(0.05, function() {
            $this->session->publish($this->gamePrivateTopic, ['UPDATE']);
        });

        $client->start();
    }
}

new Timer($argv[1]);

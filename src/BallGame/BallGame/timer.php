<?php

require dirname(__DIR__) . '/../../vendor/autoload.php';

use React\EventLoop\Factory;
use React\Stream\Stream;
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
        $loop = Factory::create();
        $client = new Client('global', $loop);
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080"));
        $client->on('open', function(ClientSession $session) {
            echo "Client opened!\n";
            $this->session = $session;
            $session->subscribe($this->gamePrivateTopic, function($args) use ($session) {
                switch($args[0]) {
                    case 'CLOSE':
                        $session->publish($this->gamePrivateTopic, ['REMOVE']);
                        exit();
                        break;
                }
            });
        });

        /*$stdin->on('data', function ($data, $stdin) {
            $data = trim($data);
            if($data == 'close')
                exit();
        });*/

        $loop->addPeriodicTimer(0.05, function() {
            $this->session->publish($this->gamePrivateTopic, ['UPDATE']);
        });

        $client->start();
    }
}

new Timer($argv[1]);

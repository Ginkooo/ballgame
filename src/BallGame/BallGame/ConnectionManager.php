<?php
namespace BallGame;

use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;
use Thruway\ClientSession;

class ConnectionManager {

    private $topic;
    private $eventHandler;

    public function __construct($eventHandler)
    {
        $this->eventHandler = $eventHandler;
        $client = new Client('global');
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080"));
        $client->on('open', function (ClientSession $session) {
            echo "Starting listenig on global\n";
            $this->eventHandler->setClientSession($session);
            $session->subscribe('global', $this->eventHandler->globalHandler);
        });
        $client->start();
    }

}

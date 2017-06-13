<?php
namespace BallGame;

require_once(__DIR__ . '/GlobalEventHandler.php');
require_once(__DIR__ . '/GameMaker.php');

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class Harness {
    private $globalEventHandler;
    private $gameMaker;

    public function __construct()
    {
        $this->gameMaker = new GameMaker();
        $this->globalEventHandler = new GlobalEventHandler($this->gameMaker);
        $client = new Client('global');
        $client->addTransportProvider(new PawlTransportProvider('ws://127.0.0.1:8080'));
        $client->on('open', function (ClientSession $session) {
            $session->subscribe('global', $this->globalEventHandler->handleEvent);
        });
        $client->start();
    }
}

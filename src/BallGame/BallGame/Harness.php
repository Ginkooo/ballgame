<?php
namespace BallGame;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/EventHandler.php');
require_once(__DIR__ . '/UserController.php');

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class Harness {
    private $eventHandler;

    public function __construct()
    {
        $this->eventHandler = new EventHandler(new UserController());

        $client = new Client('global');
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080"));

        $client->on('open', function(ClientSession $session) {
            $onEvent = function($args) {
                print("event: $args[0]");
                $response = $this->eventHandler->handleEvent($args);
                $topic = $response[0];
                $params = array_slice($response, 1);
                $session->publish($topic, $params);
                };

           $session->subscribe('global', $onEvent) ;
        });

        $client->start();
    }
}
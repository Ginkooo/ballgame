<?php
namespace BallGame;

require_once(__DIR__ . '/../../../vendor/autoload.php');

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class Connection {
    public static function makeNewClient($realm = "global") {
        $client = new Client($realm);
        echo "Making new client in $realm realm";
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080/"));
        return $client;
    }
}
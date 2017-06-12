<?php
namespace BallGame;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/Game.php');
require_once(__DIR__ . '/Connection.php');

class Harness {
    public function __construct()
    {
        $client = Connection::makeNewClient();
        $client->on('open', function ($session) {
            $onevent = function ($args) {
                if ($args[0] == 'create room') {
                    $id = uniqid();
                    echo "Creating room $id";
                    new Game($id);
                }
            };
            $session->subscribe('global', $onevent);
        });

        $client->start();
    }
}
<?php
namespace BallGame;

require __DIR__ . '/Connection.php';

class Game {

    private $client;
    private $state;
    private $session;
    private $id;
    private $players = [];

    public function __construct($id)
    {
        $this->id = $id;
        echo "creating new game $id";
        $this->globalEventHandler = function ($args) {
            switch ($args[0]) {
                case 'list games':
                    $this->session->publish($args[1], ['GAME', $this->id, $this->state]);
                    break;
                case 'join game':
                    $this->session->publish($id, [$args[1], 'joined']);
                    break;
            }
        };

        $this->gameEventHandler = function ($args) {
        };

        $client = Connection::makeNewClient();
        $client->on('open', function ($session) {
            echo "Game client opened";
            $this->session = $session;
            $session->publish('global', ['room created', $this->id]);
            $session->subscribe('global', $this->globalEventHandler);
            $session->subscribe($this->id, $this->gameEventHandler);
        });
        $client->start();

        $this->client = $client;
    }

    private function globalEventHandler($args) {
        switch($args[0]) {
            case 'list games':
                $this->session->publish($args[1], ['GAME', $this->id, $this->state]);
                break;
            case 'join game':
                $this->session->publish($id, [$args[1], 'joined']);

        }
    }

    private function gameEventHandler($args) {

    }
}

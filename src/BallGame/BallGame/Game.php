<?php
namespace BallGame;

require_once(__DIR__ . '/Ball.php');

class Game {
    public $handleEvents;
    private $ball;
    private $players = [];

    public function __construct()
    {
        echo "New game created and listening for events\n";
        $this->ball = new Ball();
        $this->handleEvents = function($args) {
            $command = $args[0];
            switch ($command) {
                case 'JOIN':
                    $playerId = $args[1];
            }
        };
    }
}

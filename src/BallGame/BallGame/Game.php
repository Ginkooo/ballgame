<?php
namespace BallGame;

require_once(__DIR__ . '/Ball.php');
require_once(__DIR__ . '/Player.php');

use React\EventLoop\Factory;

class Game {
    public $handleEvents;
    private $ball;
    private $players = [];
    private $session;

    public function __construct($session)
    {
        echo "New game created and listening for events\n";
        $this->ball = new Ball();
        $this->session = $session;
        $loop = Factory::create();
        $loop->addPeriodicTimer(0.1, function () {
            foreach($this->players as $player) {
                $this->ball->changeSpeed($player->getDownButtons());
                $this->ball->move();
            }
            $position = $this->ball->getPositon();
            var_dump($position);
            $this->session->publish('game', ['POSITION', $position]);
        });
        $loop->run();
        $this->handleEvents = function($args) {
            $command = $args[0];
            switch ($command) {
                case 'JOIN':
                    $playerId = $args[1];
                    echo "User $playerId is trying to join game\n";
                    $this->players[] = new Player($playerId);
            }
        };
    }
}

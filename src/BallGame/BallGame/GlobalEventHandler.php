<?php
namespace BallGame;

class GlobalEventHandler
{
    private $gameMaker;
    public $handleEvent;

    public function __construct($gameMaker)
    {
        $this->gameMaker = $gameMaker;
        $this->handleEvent = function ($args) {
            echo "Command: $args[0]!\n";
            $command = $args[0];
            switch ($args[0]) {
                case 'MAKE GAME':
                    echo "Trying to make a game...\n";
                    $gameRealm = $args[1];
                    $this->gameMaker->makeGame($gameRealm);
                    break;
            }
        };
    }
}


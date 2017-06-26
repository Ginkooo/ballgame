<?php

namespace BallGame;

require_once(__DIR__ . '/Ball.php');
require_once(__DIR__ . '/Player.php');

use React\EventLoop\Factory;

class Game
{
    private $ball;
    private $running = false;
    private $players = [];
    private $ownerId;
    public $handler;
    public $privateHandler;
    private $clientSession;
    private $gameTopic;
    private $gamePrivateTopic;

    public function __construct($gameTopic, $gamePrivateTopic, $ownerId, $clientSession)
    {
        echo "Game is constructed\n";
        $this->ownerId = $ownerId;
        $this->gameTopic = $gameTopic;
        $this->gamePrivateTopic = $gamePrivateTopic;
        $this->clientSession = $clientSession;
        $this->ball = new Ball;
        $this->handler = function ($args) {
            $command = $args[0];
            $userId = $args[1];
            echo "Game, of public topic $this->gameTopic recieved command $command from user $userId\n";
            if(!$this->running) {
                switch($command) {
                    case 'START':
                        if($userId === $this->ownerId) {
                            $this->running = true;
                        }
                        break;
                    case 'LOG':
                        $this->add($userId);
                        break;
                    default:
                        return;
                }
            }
            switch($command) {
                case 'PUSH':
                    $direction = $args[2];
                    echo "Player pushed $direction\n";
                    $this->players[$userId]->push($direction);
                    break;
                case 'RELEASE':
                    $direction = $args[2];
                    echo "Player released $direction\n";
                    $this->players[$userId]->release($direction);
                    break;
            }
            };
        $this->privateHandler = function ($args) {
            echo "Game of private topic $this->gamePrivateTopic recieved command $args[0]\n";
            switch($args[0]) {
                case 'UPDATE':
                    echo "update recieved\n";
                    foreach($this->players as $player) {
                        $this->ball->push($player->getPushArray());
                        var_dump($player->getPushArray());
                        $this->ball->move($player->getPushArray());
                        $this->clientSession->publish($this->gameTopic, $this->ball->getPosition());
                        $this->ball->stop();
                    }
            }
        };

        if (substr(php_uname(), 0, 7) == "Windows") {
            echo "Starting Windows timer...\n";
            echo "timer.php exists? ". file_exists(__DIR__ ."/timer.php") ? "true\n" : "false\n";
            pclose(popen("start php ". __DIR__ . "/timer.php $this->gamePrivateTopic", "r"));
            echo "Windows timer is running\n";
        }
        else {
            echo "Starting timer...\n";
            exec("php ". __DIR__ . "/timer.php $this->gamePrivateTopic > /dev/null &");
            echo "Timer is running not on Windows!\n";
        }
    }

    public function addPlayer($id) {
        echo "added played with id $id\n";
        $this->players[$id] = new Player($id);
    }
}
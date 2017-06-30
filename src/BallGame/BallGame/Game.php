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
    private $alreadyPushed;
    private $target;
    private $startTime;

    public function __construct($gameTopic, $gamePrivateTopic, $ownerId, $clientSession)
    {
        echo "Game is constructed\n";
        $this->ownerId = $ownerId;
        $this->players[$ownerId] = new Player($ownerId);
        $this->gameTopic = $gameTopic;
        $this->gamePrivateTopic = $gamePrivateTopic;
        $this->clientSession = $clientSession;
        $this->ball = new Ball;
        $this->handler = function ($args) {
            $command = $args[0];
            $userId = $args[1];
            switch ($command) {
                case 'LIST PLAYERS':
                    echo "listing players\n";
                    $this->clientSession->publish($userId, array_merge(['PLAYERS'], $this->getPlayerNames()));
                    break;
            }
            if(!$this->running) {
                switch($command) {
                    case 'JOIN':
                        if (isset($this->players[$userId])) {
                            echo "Playes tries to join as existing player\n";
                            return;
                    }
                        $this->players[$userId] = new Player($userId);
                        $this->clientSession->publish($userId, ['JOIN OK']);
                        $this->clientSession->publish($this->gameTopic, ['USER JOINED', $userId]);
                        break;
                    case 'START':
                        if($userId === $this->ownerId) {
                            $this->running = true;
                            $this->startUpdatingWatcher();
                            $this->clientSession->publish($this->gameTopic, ['START OK']);
                            $this->clientSession->publish($this->gameTopic, array_merge(['TARGET'], $this->randomizeTarget()));
                        }
                        else {
                            echo "User is not allowed to start the game\n";
                        }
                        break;
                    default:
                        return;
                }
            } else {
                switch ($command) {
                    case 'PUSH':
                        if (!$this->alreadyPushed) {
                            $this->alreadyPushed = true;
                            $this->startTime = $this->getCurrentTime();
                        }
                        if (!isset($this->players[$userId])) {
                            echo "Unexisting user can't push\n";
                            return;
                        }
                        $direction = $args[2];
                        echo "Player pushed $direction\n";
                        $this->players[$userId]->push($direction);
                        $this->clientSession->publish($userId, ['PUSH OK']);
                        break;
                    case 'RELEASE':
                        if (!isset($this->players[$userId])) {
                            echo "Unexisting user can't release\n";
                            return;
                        }
                        $direction = $args[2];
                        echo "Player released $direction\n";
                        $this->players[$userId]->release($direction);
                        $this->clientSession->publish($userId, ['RELEASE OK']);
                        break;
                }
            }
            };

        $this->privateHandler = function ($args) {
            echo "Game of private topic $this->gamePrivateTopic recieved command $args[0]\n";
            switch($args[0]) {
                case 'UPDATE':
                    echo "update recieved\n";
                    foreach($this->players as $player) {
                        $this->ball->push($player->getPushArray());
                        echo "Player of id ". $player->getId() ."\n";
                        $this->ball->move($player->getPushArray());
                        $this->clientSession->publish($this->gameTopic, $this->ball->getPosition());
                        $this->ball->stop();
                        $this->checkForWin($this->ball->getPosition());
                    }
            }
        };
    }

    private function getCurrentTime() {
        list($usec, $sec) = explode(" ", microtime());
        return (float)$usec + (float)$sec;
    }

    private function checkForWin($ballPos) {
        if(!$this->isNear($ballPos, $this->target)) {
            echo "DIDNT WIN\n";
            return;
        }
        echo "WIN\n";
        $winTime = $this->getCurrentTime();
        $playTime = $winTime - $this->startTime;
        $pid = proc_get_status($this->updater)['pid'];
        $this->clientSession->publish($this->gameTopic, ['WIN', $playTime]);
    }

    private function isNear($point1, $point2) {
        echo "POINT1 [$point1[0], $point1[1]] POINT2 [$point2[0], $point2[1]\n";
        $epsilon = 3;
        if (abs($point1[0] - $point2[0]) <= $epsilon && abs($point1[1] - $point2[1]) <= $epsilon)
            return true;
        return false;
    }

    private function startUpdatingWatcher() {
        if (substr(php_uname(), 0, 7) == "Windows") {
            echo "Starting Windows timer...\n";
            echo "timer.php exists? " . file_exists(__DIR__ . "/timer.php") ? "true\n" : "false\n";
            $this->updater = proc_open("start php " . __DIR__ . "/timer.php $this->gamePrivateTopic", [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);
            echo "Windows timer is running\n";
        } else {
            echo "Starting timer...\n";
            $this->updater = proc_open("php " . __DIR__ . "/timer.php $this->gamePrivateTopic > /dev/null &");
            echo "Timer is running not on Windows!\n";
        }
    }

    public function addPlayer($id) {
        echo "added played with id $id\n";
        $this->players[$id] = new Player($id);
    }

    public function getGameTopic() {
        return $this->gameTopic;
    }

    public function isRunning() {
        return $this->running;
    }

    public function getPlayerNames() {
        foreach ($this->players as $player) {
            $ret[] = $player->getId().($player->getId() == $this->ownerId ? " (owner)" : "");
        }
        return $ret;
    }

    public function randomizeTarget() {
        $x = rand(0, 1000);
        $y = rand(0, 1000);
        $this->target = [$x, $y];
        return [$x, $y];
    }
}
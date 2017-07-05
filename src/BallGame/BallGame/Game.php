<?php

namespace BallGame;

require_once(__DIR__ . '/Ball.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/Team.php');
require_once(__DIR__ . '/Validator.php');

use React\EventLoop\Factory;
use Thruway\ClientSession;

class Game
{
    private $ball;
    private $running = false;
    private $teams = [];
    private $players = [];
    private $ownerId;
    public $handler;
    public $privateHandler;
    /**
     * @var ClientSession
     */
    private $clientSession;
    private $gameTopic;
    private $gamePrivateTopic;
    private $alreadyPushed;
    private $target;
    private $startTime;

    public function __construct($gameTopic, $gamePrivateTopic, $ownerId, ClientSession $clientSession, Array $teams)
    {
        echo "Game is constructed\n";
        foreach ($teams as $team) {
            $this->teams[$team] = new Team($team);
        }
        reset($this->teams);
        $this->players[$ownerId] = new Player($ownerId, $this->teams[key($this->teams)]->getName());
        $this->teams[key($this->teams)]->addPlayer($this->players[$ownerId]);
        $this->ownerId = $ownerId;
        $this->gameTopic = $gameTopic;
        $this->gamePrivateTopic = $gamePrivateTopic;
        $this->clientSession = $clientSession;
        $this->ball = new Ball;
        $this->handler = function ($args) {
            $command = $args[0];
            $userId = $args[1];
            if (!Validator::validateTopic($userId))
                return;
            echo "Game, of public topic $this->gameTopic recieved command $command from user $userId\n";
            switch ($command) {
                case 'LIST PLAYERS':
                    echo "listing players\n";
                    $this->clientSession->publish($userId, array_merge(['PLAYERS'], $this->getPlayerNames()));
                    break;
                case 'LIST TEAMS':
                    echo "Listing teams\n";
                    $ret = [];
                    foreach($this->teams as $team) {
                        $ret[] = $team->getName() . " (". count($team->getPlayers()) . ")";
                    }
                    $this->clientSession->publish($userId, array_merge(['TEAMS'], $ret));
                    break;
            }
            if(!$this->running) {
                switch($command) {
                    case 'CONNECT GAME':
                        if (isset($this->players[$userId])) {
                            echo "User tries to connect game as existing player\n";
                            return;
                        }
                        $this->players[$userId] = new Player($userId);
                        $this->clientSession->publish($userId, ['CONNECT GAME OK']);
                        $this->clientSession->publish($this->gameTopic, ['USER CONNECTED GAME', $userId]);
                        break;
                    case 'JOIN':
                        if (!isset($this->players[$userId])) {
                            echo "There is no user like that in the game\n";
                            return;
                        }
                        if (!isset($args[2]))
                            return;
                        $team = $args[2];
                        if (!isset($this->teams[$team]))
                            echo "Can't join unexisting team\n";
                        $prevTeamName = $this->players[$userId]->getTeam();
                        if ($prevTeamName) {
                            $this->teams[$prevTeamName]->removePlayer($userId);
                        }
                        $this->players[$userId]->setTeam($team);
                        $this->teams[$team]->addPlayer($this->players[$userId]);
                        $this->clientSession->publish($userId, ['JOIN OK']);
                        $this->clientSession->publish($this->gameTopic, ['USER JOINED TEAM', $userId, $team]);
                        break;
                    case 'START':
                        $this->removeEmptyTeams();
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
            switch($args[0]) {
                case 'UPDATE':
                    echo "update recieved\n";
                    $gameState = [];
                    foreach ($this->teams as $team) {
                        /**
                         * @var Team $team
                         */
                        $team->pushBall();
                        $team->moveBall();
                        $gameState[] = $team->getName();
                        $ballPos = $team->getBall()->getPosition();
                        $gameState[] = $ballPos[0];
                        $gameState[] = $ballPos[1];
                        $team->getBall()->stop();
                        // Check for win and end the game if needed, sending WIN response to gamemembers
                        $won = $this->checkForWin($team->getBall()->getPosition(), $team->getName());
                        if ($won) {
                            $winTime = $this->getCurrentTime();
                            $playTime = $winTime - $this->startTime;
                            $this->clientSession->publish($this->gameTopic, ['WIN', $playTime, $team->getName()]);
                            $this->clientSession->publish($this->gamePrivateTopic, ['CLOSE']);
                            return;
                        }
                    }
                    $this->clientSession->publish($this->gameTopic, array_merge(['BALL POSITIONS'], $gameState));
            }
        };
    }

    private function removeEmptyTeams() {
        foreach($this->teams as $id => $team) {
            if (!count($team->getPlayers())) {
                unset($this->teams[$id]);
            }
        }
    }

    private function getCurrentTime() {
        list($usec, $sec) = explode(" ", microtime());
        return (float)$usec + (float)$sec;
    }

    private function checkForWin($ballPos, $teamName) {
        if(!$this->isNear($ballPos, $this->target)) {
            echo "DIDNT WIN\n";
            return false;
        }
        echo "WIN\n";
        return true;
    }

    private function isNear($point1, $point2) {
        echo "POINT1 [$point1[0], $point1[1]] POINT2 [$point2[0], $point2[1]]\n";
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
        foreach ($this->teams as $team) {
            foreach($team->getPlayers() as $player)
                $ret[] = $player->getId().($player->getId() == $this->ownerId ? " (owner)" : "")." Team ". $team->getName();
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
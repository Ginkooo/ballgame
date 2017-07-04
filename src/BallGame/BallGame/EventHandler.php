<?php
namespace BallGame;

require_once(__DIR__ . '/Validator.php');

class EventHandler
{
    public $globalHandler;
    private $clientSession;
    private $gameHandler;
    private $validIds = [];

    public function __construct($gameHandler)
    {
        $this->gameHandler = $gameHandler;
        $this->globalHandler = function ($args) {
            var_dump($args);
            $len = count($args);
            $command = $args[0];
            $userId = isset($args[1]) ? $args[1] : null;
            if (!Validator::validateTopic($userId)) {
                return;
            }
            if (!$userId) {
                echo "Invalid user id\n";
                return;
            }
            if (!in_array($userId, $this->validIds)) {
                switch($command) {
                    case 'LOG':
                        $this->clientSession->publish($userId, ["LOG OK"]);
                        echo "User logged in with id of $userId\n";
                        $this->validIds[] = $userId;
                        return;
                    default:
                        echo "Not logged user sent unvalid request\n";
                        return;
                }
            }
            switch($command) {
                case 'MAKE GAME':
                    $gameId = $args[2];
                    if(!Validator::validateTopic($gameId))
                        return;
                    if (count($args) < 4) {
                        return;
                    }
                    $teams = array_slice($args, 3);
                    if (!$teams)
                        return;
                    foreach ($teams as $team) {
                        if (!Validator::validateTeamName($team)) {
                            return;
                        }
                    }
                    echo "Trying to make a game $gameId...";
                    if($this->gameHandler->isThereGameLike($gameId)) {
                        return;
                    }
                    $this->makeGame($userId, $gameId, $teams);
                    $this->clientSession->publish($userId, ["MAKE GAME OK"]);
                    break;
                case 'LIST GAMES':
                    $response = [];
                    foreach($this->gameHandler->getGames() as $game) {
                        $response[] = array('topic' => $game->getGameTopic(), 'running' => $game->isRunning());
                    }
                    $this->clientSession->publish($userId, array_merge(['GAMES'], $response));
                    break;
                default:
                    echo "Bad command of logged user\n";
            }
        };
    }

    private function makeGame($ownerId, $gameId, $teams) {
        list($gameTopic, $gamePrivateTopic) = $this->gameHandler->createGame($ownerId, $gameId, $teams);
        echo "GameTopic is: $gameTopic, GamePrivateTopic is $gamePrivateTopic\n";
        if (!Validator::validateTopic($gameTopic))
            return;
        $this->subscribe($gameTopic, $this->gameHandler->getGame($gameTopic)->handler);
        $this->subscribe($gamePrivateTopic, $this->gameHandler->getGame($gameTopic)->privateHandler);
    }

    public function setClientSession($session) {
        echo "Setting client session\n";
        $this->clientSession = $session;
        $this->gameHandler->setClientSession($session);
    }

    private function subscribe($topic, $handler) {
        $this->clientSession->subscribe($topic, $handler);
    }
}
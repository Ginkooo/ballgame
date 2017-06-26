<?php
namespace BallGame;


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
                    echo "Trying to make a game $gameId...";
                    if($this->gameHandler->isThereGameLike($gameId)) {
                        return;
                    }
                    $response = $this->makeGame($userId, $gameId);
                    $this->clientSession->publish($userId, ["MAKE GAME OK"]);
                    break;
                case 'JOIN':
                    $gameId = $args[2];
                    echo "$userId tries to join game $gameId\n";
                    if(!$this->gameHandler->isThereGameLike($gameId)) {
                        return;
                    }
                    $this->gameHandler->getGame($gameId);
                default:
                    echo "Bad command of logged user\n";
            }
        };
    }

    private function makeGame($ownerId) {
        list($gameTopic, $gamePrivateTopic) = $this->gameHandler->createGame($ownerId);
        echo "GameTopic is: $gameTopic, GamePrivateTopic is $gamePrivateTopic\n";
        $this->subscribe($gameTopic, $this->gameHandler->getGame($gameTopic)->handler);
        $this->subscribe($gamePrivateTopic, $this->gameHandler->getGame($gameTopic)->privateHandler);
        return $gameTopic;
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
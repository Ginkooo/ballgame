<?php
namespace BallGame;

require_once(__DIR__ . '/Game.php');


class GameHandler
{
    private $games = [];
    private $clientSession;

    public function getGames() {
        return $this->games;
    }

    public function createGame($ownerId, $gameId) {
        echo "CreateGame invoked\n";
        $gameTopic = $gameId;
        $gamePrivateTopic = "prvgame1";
        $this->games[$gameTopic] = new Game($gameTopic, $gamePrivateTopic, $ownerId, $this->clientSession);
        return [$gameTopic, $gamePrivateTopic];
    }

    public function isThereGameLike($gameId) {
        return in_array($gameId, $this->games);
    }

    public function getGame($id) {
        return $this->games[$id];
    }

    public function setClientSession($session) {
        $this->clientSession = $session;
    }
}
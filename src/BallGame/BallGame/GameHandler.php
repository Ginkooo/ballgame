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

    public function createGame($ownerId) {
        echo "CreateGame invoked\n";
        $gameTopic = "game1"; //uniqid();
        $gamePrivateTopic = "prvgame1"; uniqid();
        $this->games[$gameTopic] = new Game($gameTopic, $gamePrivateTopic, $ownerId, $this->clientSession);
        $this->games[$gameTopic]->addPlayer("asdf");
        return [$gameTopic, $gamePrivateTopic];
    }

    public function getGame($id) {
        return $this->games[$id];
    }

    public function setClientSession($session) {
        $this->clientSession = $session;
    }
}
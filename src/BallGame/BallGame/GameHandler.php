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

    public function createGame($ownerId, $gameId, $teams) {
        echo "CreateGame invoked\n";
        $gameTopic = $gameId;
        $gamePrivateTopic = uniqid();
        $this->games[$gameTopic] = new Game($gameTopic, $gamePrivateTopic, $ownerId, $this->clientSession, $teams);
        print("Here i am after creating a game\n");
        return [$gameTopic, $gamePrivateTopic];
    }

    public function isThereGameLike($gameId) {
        return isset($this->games[$gameId]);
    }

    public function getGame($id) {
        return $this->games[$id];
    }

    public function setClientSession($session) {
        $this->clientSession = $session;
    }

    private function destroyGame($gameTopic) {
        unset($this->games[$gameTopic]);
    }
}
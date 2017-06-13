<?php
namespace BallGame;

require_once(__DIR__ . '/Game.php');

use Thruway\ClientSession;
use Thruway\Connection;

class GameMaker {


    public function makeGame($gameRealm) {
        $connection = new Connection([
            "realm" => $gameRealm,
            "url" => "ws://127.0.0.1:8080",
        ]);
        $connection->on("open", function (ClientSession $session) use ($gameRealm) {
            echo "Making a new game on realm $gameRealm\n";
            $session->subscribe("game", (new Game($session))->handleEvents);
        });
        $connection->open();
    }

}

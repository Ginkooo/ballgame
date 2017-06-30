<?php
namespace BallGame;


class Team
{
    private $players = [];
    private $ball;
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
        $this->ball = new Ball;
    }

    public function addPlayer($player) {
        $this->players[$player->getId()] = $player;
    }

    public function getPlayers() {
        return $this->players;
    }

    public function getPlayer($id) {
        return $this->players[$id];
    }

    public function pushBall() {
        foreach ($this->players as $player) {
            $this->ball->push($player->getPushArray());
        }
    }

    public function getBall() {
        return $this->ball;
    }

    public function moveBall() {
        foreach($this->players as $player) {
            $this->ball->move($player->getPushArray());
        }
    }
}
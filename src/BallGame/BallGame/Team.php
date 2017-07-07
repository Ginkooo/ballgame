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

    public function getName() {
        return $this->name;
    }

    public function getPlayers() {
        return $this->players;
    }

    public function removePlayer($id) {
        unset($this->players[$id]);
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
        echo "$this->name team is trying to move ball\n";
        foreach($this->players as $player) {
            echo "$this->name team, player ". $player->getId() ." is moving the ball\n";
            var_dump($player->getPushArray());
            $this->ball->move();
        }
    }
}
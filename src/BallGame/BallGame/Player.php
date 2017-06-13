<?php

namespace BallGame;


class Player
{
    private $downButtons = [
        "up" => false,
        "right" => false,
        "down" => false,
        "left" => false,
    ];

    public function pushButton($name) {
        $this->downButtons[$name] = true;
    }

    public function releaseButton($name) {
        $this->downButtons[$name] = false;
    }
}
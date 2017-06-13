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
    private $id;

    public function __construct($id)
    {
        echo "Player $id is being created\n";
        $this->id = $id;
    }

    public function getDownButtons() {
        return $this->downButtons;
    }

    public function pushButton($name) {
        $this->downButtons[$name] = true;
    }

    public function releaseButton($name) {
        $this->downButtons[$name] = false;
    }
}
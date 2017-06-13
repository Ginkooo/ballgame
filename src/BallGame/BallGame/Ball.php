<?php
namespace BallGame;

class Ball {
    private $x = 500;
    private $y = 500;
    private $speed = [0, 0];

    private function push($dimension, $updown) {
        $updown = $updown == 'up' ? 1 : -1;
        $dimension = $dimension == 'x' ? 0 : 1;
        $this->speed[$dimension] += 2 * $updown;
    }

    private function move() {
        $this->x += $this->speed[0];
        $this->y += $this->speed[1];
    }

    private function getPositon() {
        return [$this->x, $this->y];
    }
}

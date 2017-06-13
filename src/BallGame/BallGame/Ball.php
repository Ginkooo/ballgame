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

    public function move() {
        $this->x += $this->speed[0];
        $this->y += $this->speed[1];
    }

    public function getPositon() {
        return [$this->x, $this->y];
    }

    public function changeSpeed($moveArr) {
        foreach($moveArr as $direction => $bool) {
            if(!$bool)
                continue;
            $dimension = $direction == 'up' || $direction == 'down' ? 'y' : 'x';
            switch($direction) {
                case 'up':
                    $this->push($dimension, 'down');
                    break;
                case 'down':
                    $this->push($dimension, 'up');
                    break;
                case 'right':
                    $this->push($dimension, 'up');
                    break;
                case 'left':
                    $this->push($dimension, 'down');
                    break;
            }
        }
    }
}

<?php
namespace BallGame;


class Ball
{
    private $position = [500, 500];
    private $speed = [0, 0];
    private $previousPushArray = [
        'up' => false,
        'down' => false,
        'left' => false,
        'right' => false,
    ];

    public function move() {
        for ($i = 0; $i < 2; $i++) {
            $this->position[$i] += $this->speed[$i];
        }
    }

    public function stop() {
        $this->speed[0] = $this->speed[1] = 0;
    }

    public function getPosition() {
        return $this->position;
    }

    public function push($pushArray) {
        $unit = 6;
        $this->processPushArray($pushArray, $this->previousPushArray);
        $this->previousPushArray = $pushArray;
        foreach($pushArray as $direction => $bool) {
            echo "Ball is being moved left\n";
            if(!$bool)
                continue;
            switch($direction) {
                case 'up':
                    $this->speed[1] -= $unit;
                    break;
                case 'down':
                    $this->speed[1] += $unit;
                    break;
                case 'left':
                    $this->speed[0] -= $unit;
                    break;
                case 'right':
                    $this->speed[0] += $unit;
                    break;
            }
        }
    }

    private function processPushArray(&$pushArray, &$previousPushArray) {
    }
}
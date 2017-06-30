<?php
/**
 * Created by PhpStorm.
 * User: Szymon
 * Date: 14.06.2017
 * Time: 12:40
 */

namespace BallGame;


class Player
{
    private $id;
    private $team;

    private $pushArray = [
        'up' => false,
        'down' => false,
        'left' => false,
        'right' => false,
        ];

    public function __construct($id, $team)
    {
        $this->team = $team;
        $this->id = $id;
    }

    public function push($direction) {
        $this->team = $team;
        echo "Player $this->id is pushing $direction now\n";
        foreach($this->pushArray as $key => $value) {
            $this->pushArray[$key] = false;
        }
        $this->pushArray[$direction] = true;
    }

    public function release($direction) {
        $this->pushArray[$direction] = false;
    }

    public function getPushArray() {
        return $this->pushArray;
    }

    public function getId() {
        return $this->id;
    }

    public function getTeam() {
        return $this->team;
    }
}
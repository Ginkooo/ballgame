<?php

namespace BallGame;


class Validator
{
    //Dictionary made for performace, hashtables are fast
    public static $validTeamNames = [
        'red' => true,
        'blue' => true,
        'green' => true,
        'yellow' => true,
        'pink' => true,
        ];

    public static function validateTopic(&$topicName) {
        if (!$topicName)
            return false;
        if (preg_match('/^[\da-z]*$/', $topicName))
            return true;
        echo "$topicName is a bad name\n";
        return false;
    }

    public static function validateTeamName(&$teamName) {
        if (!$teamName)
            return false;
        if (isset(static::$validTeamNames[$teamName]))
            return true;
        return false;
    }
}
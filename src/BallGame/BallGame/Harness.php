<?php
namespace BallGame;

require_once(__DIR__ . '/EventHandler.php');
require_once(__DIR__ . '/ConnectionManager.php');
require_once(__DIR__ . '/GameHandler.php');

class Harness
{

    public function __construct()
    {
        new ConnectionManager(new EventHandler(new GameHandler()));
    }
}

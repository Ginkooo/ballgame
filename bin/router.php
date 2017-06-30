<?php
namespace BallGame;

require __DIR__ . '/../vendor/autoload.php';

use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;

$router = new Router();
$transportProvider = new RatchetTransportProvider("0.0.0.0", 8080);

$router->addTransportProvider($transportProvider);

$router->start();

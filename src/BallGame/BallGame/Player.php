<?php
namespace BallGame;

require __DIR__ . '/Connection.php';

class Player {
    private $session;
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
        $client = Connection::makeNewClient();
        $client->on('open', function ($session) {
            $this->session = $session;
        });
    }
}

<?php
namespace BallGame;

class User {
    public $name;
    public $id;
    public $secret;
    public $status;
    public $session;

    public function __construct($id, $name = 'anonymous')
    {
        $this->id = $id;
        $this->secret = $uniqid();
        $this->name = $name;
        $this->status = 'logged';
    }
}

class UserController {
    private $users = [];

    public function addUser($id) {
        $this->assertUnoccupied($id);
        $user = new User($id);
        $this->users[] = $user;
    }

    public function getUser($id) {
        return $this->users[$id];
    }

    public function getSecret($id) {
        return $this->users[$id]->secret;
    }

    public function assertUnoccupied($id) {
        if(isset($this->users[$id])) {
            throw new Exception('User exists');
        }
        return false;
    }

    public function removeUser($id) {
        unset($this->users[$id]);
    }
}

<?php
namespace BallGame;

require_once(__DIR__ . '/Event.php');

class EventHandler {
    private $userController;

    public function __construct($userController)
    {
        $this->userController = $userController;
    }

    public function makeResponse($args)
    {
        $eventName = $args[0];
        $sender = $args[1];
        $content = $args[2];
        $reciever = $args[3];
        $secret = $args[4];
        $event = new Event($eventName, $sender, $reciever, $content, $secret);
        //TODO: Check if sender is eligible to send message to reciever topic
            switch ($args[0]) {
                case 'HELLO':
                    $body = $this->hello();
                case 'LOGIN':
                    $response = $this->login($id);
                    break;
            }
    }

    private function hello($name) {
        return ['HELLO', 'global', $name];
    }

    private function login($id) {
        $this->userController->addUser($userId);
        $secret = $this->userController->getUawe($userId);
        return ["LOGGEDIN", $userId, $secret];
    }
}

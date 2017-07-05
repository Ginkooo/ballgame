globals = {};
$.onload = function() {
    var client = new Client('127.0.0.1', 8080);
    var user = new User();
    var game = new Game();
    var viewController = new ViewController(user);
    //var scale = 3;
    //var ballRadius = 5;
    //var game = new Game(scale, ballRadius);
}();

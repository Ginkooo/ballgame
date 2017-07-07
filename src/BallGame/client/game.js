function Game(scale, ballRadius) {
    var gameTopic = null;
    var target = [];

    var elements = {
        gameWindow: $("#gameWindow")
    }

    var indicators = {
        win: $("#winHeader")
    }

    var eventHandlers = {
        'START OK': function() {
            console.log("Trying to start a game");
            // Begin game here
        },
        'TARGET': function(args) {
            console.log("Trying to place a target");
            var x = args[1];
            var y = args[2];
            target = [x, y];
        },
        'WIN': function(args) {
            console.log("WON THE GAME!");
            var team = args[2];
            var time = args[1];
            elements.gameWindow.remove();
            indicators.html(team + " WON IN " + time + " SECONDS!!");
        },
        'BALL POSITIONS': function() {},
    }

    globals.gameEvent = self.onEvent = function(args) {
        var cmd = args[0];
        console.log("Game event:", cmd);
        if (!eventHandlers[cmd]) {
            console.log("Bad response");
            return;
        }
        eventHandlers[cmd]();
    }
}
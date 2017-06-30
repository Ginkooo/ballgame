var connection = new autobahn.Connection({
    url: "ws://10.5.30.151:8080/",
    realm: 'global'
});

window.connection = connection;

window.gameRunning = false;
window.scale = 400/1000;
window.ballRadius = 5;

connection.onopen = function (sess, details) {

    console.log("Opened connection");

    window.onevent = function onevent(args) {
        console.log("Global Event: ", JSON.stringify(args));
    }

    window.userevent = function userevent(args) {
        console.log("User Event: ", JSON.stringify(args));
        switch(args[0]) {
            case 'LOG OK':
                window.userTopic = $('#userTopic').val();
                console.log("Setting userTopic header");
                $('#userTopicHeader').html('Logged as: ' + window.userTopic);
                break;
            case 'MAKE GAME OK':
                window.gameTopic = $('#gameTopic').val();
                $('#gameTopicHeader').html('Game: ' + window.gameTopic + ' (owner)');
                $('#startGameButton').css('display', '');
                break;
            case 'GAMES':
                console.log("listing games");
                $('#gameList').html('');
                for(var i=1; i<args.length; i++) {
                    $('#gameList').append('<li>' + args[i].topic + " - " + (args[i].running ? '(ingame)' : '(lobby)') + '</li>');
                }
                break;
            case 'JOIN OK':
                $('#gameTopicHeader').html('Game: ' + window.gameTopic);
                break;
            case 'PLAYERS':
                $('#playerList').html('');
                for(var i=1; i<args.length; i++) {
                    $('#playerList').append('<li>' + args[i] + '</li>');
                }
        }
    }

    window.gameevent = function gameevent(args) {
        console.log('Game event:', JSON.stringify(args));
        if (args.length == 3 && !isNaN(args[0])) {
            window.redraw(args[0], args[1], args[2]);
            $('#ballPosition').html("["+args[0]+", "+args[1]+"]");
        }
        switch(args[0]) {
            case 'START OK':
                console.log("Trying to start a game");
                window.beginGame();
                console.log('ima here');
                break;
            case 'TARGET':
                console.log("Trying to place a target");
                window.placeTarget(args[1], args[2]);
                break;
            case 'WIN':
                console.log('WON THE GAME!');
                $('#gameWindow').remove();
                $('#winHeader').html("WON IN " + args[1] + " SECONDS!!");
                window.connection.close();
        }
    }

    window.prvgameevent = function prvgameevent(args) {
        console.log("Private game Event: ", JSON.stringify(args));
    }

    window.userprvevent = function userprvevent(args) {
        console.log("Private user event ", JSON.stringify(args))
    }

    sess.subscribe('global', window.onevent);
    window.session = sess;
};

connection.open();

window.beginGame = function () {
    window.gameRunning = true;
    console.log("Beginning a game");
    window.canvas = document.getElementById("gameWindow");
    window.context = canvas.getContext("2d");
}

window.redraw = function(x, y, team) {
    window.context.clearRect(0, 0, window.canvas.width, window.canvas.height);
    window.moveBall(x, y, team);
    window.redrawTarget(window.target[0], window.target[1]);
}

window.placeTarget = function (x, y) {
    window.target = [x, y];
    console.log("Target is being placed!");
    window.context.beginPath();
    window.context.arc(x * window.scale, y * window.scale, window.ballRadius, 0, 2 * Math.PI);
    window.context.fillStyle = 'black';
    window.context.fill();
    window.context.stroke();
}

window.redrawTarget = function(x, y) {
    console.log("redrawing target!");
    window.context.beginPath();
    window.context.arc(target[0] * window.scale, target[1] * window.scale, window.ballRadius, 0, 2 * Math.PI);
    window.context.fillStyle = 'black';
    window.context.fill();
    window.context.stroke();
}

window.moveBall = function(x, y, team) {
    console.log("Ball moved!");
    window.context.beginPath();
    window.context.arc(x * window.scale, y * window.scale, window.ballRadius, 0, 2 * Math.PI);
    window.context.fillStyle = team;
    window.context.fill();
    window.context.stroke();
}

$('#loginButton').click(function () {
    console.log('Logging as', $('#userTopic').val())
    window.session.subscribe($('#userTopic').val(), window.userevent);
    window.session.publish('global', ['LOG', $('#userTopic').val()]);
});

$('#makeGameButton').click(function() {
    console.log("Making game of topic", $('#gameTopic').val());
    window.session.subscribe($('#gameTopic').val(), window.gameevent)
    window.session.publish('global', ['MAKE GAME', window.userTopic, $('#gameTopic').val()]);
});

$('#startGameButton').click(function () {
    console.log("Starting game");
    window.session.publish(window.gameTopic, ['START', window.userTopic]);
});

$(document).keydown(function(event) {
    if(!window.gameRunning)
        return;
    switch(event.which) {
        case 65: //left
            if(!window.left)
                window.keyPressed('left');
            window.left = true;
            break;
        case 87: //up
            if(!window.up)
                window.keyPressed('up');
            window.up = true;
            break;
        case 68: //right
            if(!window.right)
                window.keyPressed('right');
            window.right = true;
            break;
        case 83: //down
            if(!window.down)
                window.keyPressed('down');
            window.down = true;
            break;
    }
});

$(document).keyup(function(event) {
    if(!window.gameRunning)
        return;
    switch(event.which) {
        case 65: //left
            if(window.left)
                window.keyReleased('left');
            window.left = false;
            break;
        case 87: //up
            if(window.up)
                window.keyReleased('up');
            window.up = false;
            break;
        case 68: //right
            if(window.right)
                window.keyReleased('right');
            window.right = false;
            break;
        case 83: //down
            if(window.down)
                window.keyReleased('down');
            window.down = false;
            break;
    }
});

window.keyPressed = function(direction) {
    console.log("PUSHING", direction);
    window.session.publish(window.gameTopic, ['PUSH', window.userTopic, direction]);
}

window.keyReleased = function(direction) {
    console.log("RELEASING", direction);
    window.session.publish(window.gameTopic, ['RELEASE', window.userTopic, direction]);
}

$('#listGamesButton').click(function() {
   window.session.publish('global', ['LIST GAMES', window.userTopic]);
});

$('#joinGameButton').click(function() {
    window.gameTopic = $('#gameTopicJoin').val();
    console.log('JOINing game:', window.gameTopic);
    window.session.subscribe(window.gameTopic, window.gameevent);
    window.session.publish(window.gameTopic, ['JOIN', window.userTopic]);
});

$('#listPlayersButton').click(function () {
    console.log("Sending request for player list");
    window.session.publish(window.gameTopic, ['LIST PLAYERS', window.userTopic]);
});



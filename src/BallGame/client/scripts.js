var connection = new autobahn.Connection({
    url: "ws://10.5.30.151:8080/",
    realm: 'global'
});

globals = {};

globals.connection = connection;

globals.gameRunning = false;
globals.scale = 400/1000;
globals.ballRadius = 5;

connection.onopen = function (sess, details) {

    console.log("Opened connection");

    globals.onevent = function onevent(args) {
        console.log("Global Event: ", JSON.stringify(args));
    }

    globals.userevent = function userevent(args) {
        console.log("User Event: ", JSON.stringify(args));
        switch(args[0]) {
            case 'LOG OK':
                globals.userTopic = $('#userTopic').val();
                console.log("Setting userTopic header");
                $('#userTopicHeader').html(globals.userTopic);
                break;
            case 'MAKE GAME OK':
                globals.gameTopic = $('#gameTopic').val();
                $('#gameTopicHeader').html(globals.gameTopic + ' (owner)');
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
                $('#gameTopicHeader').html('Game: ' +globals.gameTopic);
                break;
            case 'PLAYERS':
                $('#playerList').html('');
                for(var i=1; i<args.length; i++) {
                    $('#playerList').append('<li>' + args[i] + '</li>');
                }
                break;
            case 'TEAMS':
                console.log('Listing teams');
                $('#teamList').html('');
                for (var i=1; i<args.length; i++) {
                    $('#teamList').append('<li>' + args[i] + '</li>');
                }
                break;
            case 'CONNECT GAME OK':
                console.log("Successfully connected to a game");
                $('#gameTopicHeader').html(globals.connectingGameTopic)
                globals.gameTopic = globals.connectingGameTopic;
        }
    }

    globals.gameevent = function gameevent(args) {
        console.log('Game event:', JSON.stringify(args));
        switch (args[0]) {
            case 'START OK':
                console.log("Trying to start a game");
                globals.beginGame();
                console.log('ima here');
                break;
            case 'TARGET':
                console.log("Trying to place a target");
                globals.placeTarget(args[1], args[2]);
                break;
            case 'WIN':
                console.log('WON THE GAME!');
                $('#gameWindow').remove();
                $('#winHeader').html(args[2] + " WON IN " + args[1] + " SECONDS!!");
                globals.connection.close();
                break;
            case 'BALL POSITIONS':
                //['BALL POSITION, 'red', '1', '1', 'blue', '1', '1']
                var positions = {};
                for (var i = 1; i < args.length; i += 3) {
                    console.log("Setting " + args[i] + " to " + args[i + 1] + ", " + args[i + 2]);
                    positions[args[i]] = [args[i + 1], args[i + 2]];
                }
                globals.redraw(positions);
                break;
        }
    }

    globals.prvgameevent = function prvgameevent(args) {
        console.log("Private game Event: ", JSON.stringify(args));
    }

    globals.userprvevent = function userprvevent(args) {
        console.log("Private user event ", JSON.stringify(args))
    }

    sess.subscribe('global',globals.onevent);
    globals.session = sess;
};

connection.open();

globals.getRequriredTeamNames = function () {
    var teamNameText = $('#teamNames');
    var teams = teamNameText.val().split(',');
    console.log(JSON.stringify(teams));
    return teams;
}

globals.beginGame = function () {
    globals.gameRunning = true;
    console.log("Beginning a game");
    globals.canvas = document.getElementById("gameWindow");
    globals.context = globals.canvas.getContext("2d");
}

globals.redraw = function(ballPositions) {
    console.log(JSON.stringify(ballPositions));
    globals.context.clearRect(0, 0,globals.canvas.width,globals.canvas.height);
    globals.moveBalls(ballPositions);
    globals.redrawTarget(globals.target[0],globals.target[1]);
}

globals.placeTarget = function (x, y) {
    globals.target = [x, y];
    console.log("Target is being placed in ", x, y);
    globals.context.beginPath();
    globals.context.arc(x *globals.scale, y *globals.scale,globals.ballRadius, 0, 2 * Math.PI);
    globals.context.fillStyle = 'black';
    globals.context.fill();
    globals.context.stroke();
}

globals.redrawTarget = function(x, y) {
    console.log("redrawing target!");
    globals.context.beginPath();
    globals.context.arc(x *globals.scale, y *globals.scale,globals.ballRadius, 0, 2 * Math.PI);
    globals.context.fillStyle = 'black';
    globals.context.fill();
    globals.context.stroke();
}

globals.moveBalls = function(ballPositions) {
    console.log("Ball " + team + " moved!");
    $.each(ballPositions, function (key, value) {
        globals.context.beginPath();
        globals.context.arc(value[0] *globals.scale, value[1] *globals.scale,globals.ballRadius, 0, 2 * Math.PI);
        globals.context.fillStyle = key;
        globals.context.fill();
        globals.context.stroke();
    })
}

$('#loginButton').click(function () {
    console.log('Logging as', $('#userTopic').val())
    globals.session.subscribe($('#userTopic').val(),globals.userevent);
    globals.session.publish('global', ['LOG', $('#userTopic').val()]);
});

$('#makeGameButton').click(function() {
    console.log("Making game of topic", $('#gameTopic').val());
    globals.session.subscribe($('#gameTopic').val(),globals.gameevent)
    globals.session.publish('global', ['MAKE GAME',globals.userTopic, $('#gameTopic').val()].concat(globals.getRequriredTeamNames()));
});

$('#startGameButton').click(function () {
    console.log("Starting game");
    globals.session.publish(globals.gameTopic, ['START',globals.userTopic]);
});

$('#listTeamsButton').click(function() {
    console.log('Sending LIST TEAMS');
    globals.session.publish(globals.gameTopic, ['LIST TEAMS', globals.userTopic]);
});

$('#connectGameButton').click(function() {
    console.log("Connecting a game\n");
    globals.connectingGameTopic = $('#gameTopicConnect').val();
    globals.session.publish(globals.connectingGameTopic, ['CONNECT GAME', globals.userTopic]);
});

$(document).keydown(function(event) {
    if(!globals.gameRunning)
        return;
    switch(event.which) {
        case 65: //left
            if(!globals.left)
                globals.keyPressed('left');
            globals.left = true;
            break;
        case 87: //up
            if(!globals.up)
                globals.keyPressed('up');
            globals.up = true;
            break;
        case 68: //right
            if(!globals.right)
                globals.keyPressed('right');
            globals.right = true;
            break;
        case 83: //down
            if(!globals.down)
                globals.keyPressed('down');
            globals.down = true;
            break;
    }
});

$(document).keyup(function(event) {
    if(!globals.gameRunning)
        return;
    switch(event.which) {
        case 65: //left
            if(globals.left)
                globals.keyReleased('left');
            globals.left = false;
            break;
        case 87: //up
            if(globals.up)
                globals.keyReleased('up');
            globals.up = false;
            break;
        case 68: //right
            if(globals.right)
                globals.keyReleased('right');
            globals.right = false;
            break;
        case 83: //down
            if(globals.down)
                globals.keyReleased('down');
            globals.down = false;
            break;
    }
});

globals.keyPressed = function(direction) {
    console.log("PUSHING", direction);
    globals.session.publish(globals.gameTopic, ['PUSH',globals.userTopic, direction]);
}

globals.keyReleased = function(direction) {
    console.log("RELEASING", direction);
    globals.session.publish(globals.gameTopic, ['RELEASE',globals.userTopic, direction]);
}

$('#listGamesButton').click(function() {
   globals.session.publish('global', ['LIST GAMES',globals.userTopic]);
});

$('#joinTeamButton').click(function() {
    console.log('JOINing team:',$('#team').val());
    globals.session.subscribe(globals.gameTopic,globals.gameevent);
    globals.session.publish(globals.gameTopic, ['JOIN',globals.userTopic, $('#team').val() === '' ? 'red' : $('#team').val()]);
});

$('#listPlayersButton').click(function () {
    console.log("Sending request for player list");
    globals.session.publish(globals.gameTopic, ['LIST PLAYERS',globals.userTopic]);
});

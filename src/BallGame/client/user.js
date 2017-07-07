function User() {
    self = this;
    var userTopic;
    var gameTopic;
    var connectingGameTopic;
    var teamName;

    var logged = false;
    var inGame = false;

    var textFields = {
        topicName: $('#userTopic'),
        gameTopicName: $('#gameTopic'),
        connectingGameName: $('#gameTopicConnect'),
        team: $('#team')
    };

    var buttons = {
        login: $('#loginButton'),
        startGame: $('#startGameButton')
    };

    var indicators = {
        userName: $('#userTopicHeader'),
        gameName: $('#gameTopicHeader'),
        gameList: $('#gameList'),
        playerList: $('#playerList'),
        teamList: $('#teamList'),
    };

    self.clickHandlers = {
        onLoginClick: function() {
            globals.userTopic = userTopic = textFields.topicName.val();
            globals.session.subscribe(userTopic, self.onevent);
            globals.session.publish('global', ['LOG', userTopic])
        },

        onMakeGameClick: function() {
            console.log("Making game of topic", $('#gameTopic').val());
            gameTopic = textFields.gameTopicName.val();
            globals.session.subscribe(gameTopic, globals.gameEvent);
            globals.session.publish('global', ['MAKE GAME', userTopic, gameTopic]);
        },

        onStartGameClick: function () {
            console.log("Starting game");
            globals.session.publish(gameTopic, ['START',userTopic]);
        },

        onTeamListClick: function () {
            console.log('Sending LIST TEAMS');
            globals.session.publish(gameTopic, ['LIST TEAMS', userTopic]);
        },

        onConnectGameClick: function () {
            console.log("Connecting a game\n");
            connectingGameTopic = textFields.connectingGameName.val();
            globals.session.publish(connectingGameTopic, ['CONNECT GAME', userTopic]);
        },

        onJoinTeamClick: function () {
            var team = textFields.team.val();
            console.log('JOINing team:', team);
            globals.session.subscribe(gameTopic, globals.gameEvent);
            globals.session.publish(gameTopic, ['JOIN', userTopic, team === '' ? 'red' : $('#team').val()]);
        },

        onListGamesClick: function () {
            globals.session.publish('global', ['LIST GAMES', userTopic]);
        },

        onListPlayersClick: function () {
            console.log("Sending request for player list");
            globals.session.publish(gameTopic, ['LIST PLAYERS', userTopic]);
        }
    }

    var responseHandlers = {
        'LOG OK': function() {
            indicators.userName.html(userTopic);
            logged = true;
        },

        'MAKE GAME OK': function() {
            global.gameTopic = gameTopic = textFields.gameTopicName.val();
            indicators.gameName.html(gameTopic + ' (owner)');
            buttons.startGame.css('display', '');
        },

        'GAMES': function(args) {
            console.log("listing games");
            indicators.gameList.html('');
            for (var i = 1; i < args.length; i++) {
                indicators.gameList.append('<li>' + args[i].topic + " - " + (args[i].running ? '(ingame)' : '(lobby)') + '</li>');
            }
        },

        'JOIN OK': function () {
            indicators.gameName.html(gameTopic);
        },

        'PLAYERS': function (args) {
            indicators.playerList.html('');
            for(var i=1; i<args.length; i++) {
                indicators.playerList.append('<li>' + args[i] + '</li>');
            }
        },

        'TEAMS': function (args) {
            console.log('Listing teams');
            indicators.teamList.html('');
            for (var i = 1; i < args.length; i++) {
                indicators.teamList.append('<li>' + args[i] + '</li>');
            }
        },

        'CONNECT GAME OK': function (args) {
            console.log("Successfully connected to a game");
            indicators.gameName.html(connectingGameTopic)
            globals.gameTopic = gameTopic = connectingGameTopic;
        },
    }

    self.getUserTopic = function() {
        return userTopic;
    };

    globals.userEvent = self.onEvent = function (args) {
        console.log("User Event: ", JSON.stringify(args));
        if (!responseHandlers[args[0]]) {
            console.log("Bad response");
            return;
        }
        responseHandlers[args[0]](args);
        return;
    };
};
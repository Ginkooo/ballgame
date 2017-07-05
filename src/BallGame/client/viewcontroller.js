function ViewController(user) {
    //button and their handlers
    var buttons = {
        login: [$('#loginButton'), user.clickHandlers.onLoginClick],
        startGame: [$('#startGameButton'), user.clickHandlers.onStartGameClick],
        connectGame: [$('#connectGameButton'), user.clickHandlers.onConnectGameClick],
        joinTeam: [$('#joinTeamButton'), user.clickHandlers.onJoinTeamClick],
        listGames: [$('#listGamesButton'), user.clickHandlers.onListGamesClick],
        listPlayers: [$('#listPlayersButton'), user.clickHandlers.onListPlayersClick],
        listTeams: [$('#listTeamsButton'), user.clickHandlers.onListGamesClick],
        makeGame: [$('#makeGameButton'), user.clickHandlers.onMakeGameClick],
    };

    for (var button in buttons) {
        if (buttons.hasOwnProperty(button)) {
            buttons[button][0].click(buttons[button][1]);
        }
    }
}
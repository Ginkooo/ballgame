var connection = new autobahn.Connection({
    url: "ws://127.0.0.1:8080/",
    realm: 'global'
});

connection.onopen = function (sess, details) {

    function onevent(args) {
        console.log("Event: ", args[0]);
    }

    sess.subscribe('global', onevent);
    window.session = sess;
};

var topic = $('#topic');
var params = $("#params");
var publish = $('#publish');
var gameparams = $('#gameparams');
var realm = $('#realm');
var gamepublish = $('#gamepublish');

publish.click(function () {
    window.session.publish(topic.val(), JSON.parse(params.val()));
});

gamepublish.click(function() {
    console.log("gamepublish clicked");
    var connection = new autobahn.Connection({
        url: "ws://127.0.0.1:8080/",
        realm: realm.val()
    });

    connection.onopen = function (sess, details) {
        console.log('Game conenction opened!');
        function onevent(args) {
            console.log("Event: ", args[0]);
        }

        sess.subscribe('global', onevent);
        sess.publish('game', JSON.parse(gameparams.val()));
    };

    connection.open();
});

connection.open();

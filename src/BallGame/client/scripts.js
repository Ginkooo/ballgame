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
var gameparams = $('#gparams');
var gametopic = $('#gtopic');
var mepublish = $('#gpublish');

console.log(mepublish);
console.log("here");

mepublish.click(function () {
    console.log("cliekc!");
    window.session.publish(gametopic.val(), JSON.parse(gameparams.val()));
});

publish.click(function () {
    window.session.publish(topic.val(), JSON.parse(params.val()));
});


connection.open();
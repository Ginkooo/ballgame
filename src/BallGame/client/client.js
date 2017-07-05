function Client(host, port) {

    var self = this;

    self.connection = new autobahn.Connection({
        url: 'ws://' + host + ':' + port,
        realm: 'global'
    });

    self.connection.onopen = function(sess, details) {
        console.log('Opened session');
        sess.subscribe('global', onevent);
        globals.session = sess;
    };

    var onevent = function onevent(args) {
        console.log("Global Event: ", JSON.stringify(args));
    }

    var prvgameevent = function prvgameevent(args) {
        console.log("Private game Event: ", JSON.stringify(args));
    }

    var userprvevent = function userprvevent(args) {
        console.log("Private user event ", JSON.stringify(args))
    }
    self.connection.open();
}

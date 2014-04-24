var BOSH_SERVICE = 'http://192.237.219.76/http-bind';
var connection = null;

function log(msg) {
    $('#log').append('<div></div>').append(document.createTextNode(msg));
}

function onConnect(status) {
    if (status == Strophe.Status.CONNECTING) {
        log('Strophe is connecting.');
    } else if (status == Strophe.Status.CONNFAIL) {
        log('Strophe failed to connect.');
        $('#connect').get(0).value = 'connect';
    } else if (status == Strophe.Status.DISCONNECTING) {
        log('Strophe is disconnecting.');
    } else if (status == Strophe.Status.DISCONNECTED) {
        log('Strophe is disconnected.');
        $('#connect').get(0).value = 'connect';
    } else if (status == Strophe.Status.CONNECTED) {
        log('Strophe is connected.');
        log('ECHOBOT: Send a message to ' + connection.jid +
            ' to talk to me.');

        //Init functions (plugins)
        connection.muc.init(connection);


        connection.addHandler(onMessage, null, 'message', null, null, null);
        connection.send($pres().tree());
//    connection.xmlInput = onXmlInput;
        iq = $iq({type: 'get'}).c('query', {xmlns: 'jabber:iq:roster'});
//    connection.sendIQ(iq, your_roster_callback_function);
        connection.sendIQ(iq, RosterObj.on_roster);
        connection.addHandler(RosterObj.on_roster_changed,
            "jabber:iq:roster", "iq", "set");


    }
}

//
//
//function onXmlInput(data) {
//    Strophe.forEachChild(data, "presence", function(child) {
//        var from = child.getAttribute('from');
//
//        from = from.substring(0, from.indexOf('@'));
//        //'type' will contain "unavailable" when offline and no attribute 'type' when online
//        if (!child.hasAttribute('type')) {
//            console.log(from)
//        } else {
//            console.log(from);
//        }
//    });
//}


function onMessage(msg) {
    console.log('onMessage call');
    var to = msg.getAttribute('to');
    var from = msg.getAttribute('from');
    var type = msg.getAttribute('type');
    var elems = msg.getElementsByTagName('body');

    if (type == "chat" && elems.length > 0) {
        var body = elems[0];

        log('ECHOBOT: I got a message from ' + from + ': ' +
            Strophe.getText(body));

//	var reply = $msg({to: from, from: to, type: 'chat'})
//            .cnode(Strophe.copyElement(body));
//	connection.send(reply.tree());
//
//	log('ECHOBOT: I sent ' + from + ': ' + Strophe.getText(body));
    } else {
        //User invited
        InviteReason = msg.getElementsByTagName('invite')[0].childNodes[0].innerHTML;
        InviteFrom = msg.getElementsByTagName('invite')[0].getAttributeNode("from").nodeValue;
//        console.log(msg.getElementsByTagName('invite').getElementsByTagName('from'));
        if (msg.getElementsByTagName('invite') && to!=InviteFrom) {
            if (confirm('Join group chat, invited from ' + InviteFrom + '. Reason - ' + InviteReason)) {
                alert('connection');
                Rooms.room = from;
                Rooms.connection = connection;
                console.log(from);
                connection.muc.join(from, connection.jid);

                Rooms.joined = true;
                $(document).trigger('user_joined', connection.jid);

                Rooms.connection.addHandler(Rooms.on_presence,
                    null, "presence");
                Rooms.connection.addHandler(Rooms.on_invite,
                    null, "message",null,'invite');
                Rooms.connection.addHandler(Rooms.on_public_message,
                    null, "message", "groupchat");
                Rooms.connection.addHandler(Rooms.on_private_message,
                    null, "message", "chat");

            } else {
                alert('no');
                return true;
            }
        }

    }

    // we must return true to keep the handler alive.  
    // returning false would remove it after it finishes.
    return true;
}


//function sendMessage(message) {
//    var msg = $msg({to: 'test@192.237.219.76', from: connection.jid, type: 'chat'}).c('body').t(document.URL + '\n' + message);
//    connection.send(msg.tree());
//
//    AddText(message, 'out');
//    $('input#message').val('');
//    return;
//}

function sendMessage() {
    var message = $('#message').get(0).value;
    var to = 'test@192.237.219.76';
    if (message && to) {
        var reply = $msg({
            to: to,
            type: 'chat'
        })
            .cnode(Strophe.xmlElement('body', message)).up()
            .c('active', {xmlns: "http://jabber.org/protocol/chatstates"});

        connection.send(reply);

        log('I sent ' + to + ': ' + message);
    }
}

$(document).ready(function () {
    connection = new Strophe.Connection(BOSH_SERVICE);
    RosterObj.connection = connection;

    // Uncomment the following lines to spy on the wire traffic.
    //connection.rawInput = function (data) { log('RECV: ' + data); };
    //connection.rawOutput = function (data) { log('SEND: ' + data); };

    // Uncomment the following line to see all the debug output.
    //Strophe.log = function (level, msg) { log('LOG: ' + msg); };


    $('#connect').bind('click', function () {
        var button = $('#connect').get(0);
        if (button.value == 'connect') {
            button.value = 'disconnect';

            connection.connect($('#jid').get(0).value,
                $('#pass').get(0).value,
                onConnect);
        } else {
            button.value = 'connect';
            connection.disconnect();
        }
    });
    $('#send').bind('click', function () {
        sendMessage();
    });
    $('#sendRoom').bind('click', function () { //Creates room and invites test user
        Rooms.joined = false;
        Rooms.connection = connection;
        Rooms.participants = [];
        var d = $pres({"from": "vadim@192.237.219.76", "to": $('#room').val() +"@conference.192.237.219.76/vadim"})
            .c("x", {"xmlns": "http://jabber.org/protocol/muc"});
        if ($('#room').val()) {
        connection.send(d.tree());
        connection.muc.createInstantRoom($('#room').val() +"@conference.192.237.219.76/vadim");
        connection.muc.invite($('#room').val() +"@conference.192.237.219.76", 'test@192.237.219.76', 'her');
        Rooms.room = $('#room').val() +"@conference.192.237.219.76";
        connection.muc.join(Rooms.room, connection.jid);

        } else {
            Rooms.room = "tstroom@conference.192.237.219.76";
            connection.muc.join("tstroom@conference.192.237.219.76",connection.jid);

        }

        Rooms.joined = true;
        $(document).trigger('user_joined', connection.jid);

        Rooms.connection.addHandler(Rooms.on_presence,
            null, "presence");
        Rooms.connection.addHandler(Rooms.on_invite,
            null, "message",null,'invite');
        Rooms.connection.addHandler(Rooms.on_public_message,
            null, "message", "groupchat");
        Rooms.connection.addHandler(Rooms.on_private_message,
            null, "message", "chat");
//
//        Rooms.connection.send(
//            $pres({
//                to: Rooms.room + "/" + Rooms.nickname
//            }).c('x', {xmlns: Rooms.NS_MUC}));

    });

    $('.roster-contact').live('click', function () {
        console.log('start chating with ' + $(this).find(".roster-name").text());
        var jid = $(this).find(".roster-jid").text();
        var name = $(this).find(".roster-name").text();
        var jid_id = RosterObj.jid_to_id(jid);

    });


    $('#input').keypress(function (ev) {
        if (ev.which === 13) {
            ev.preventDefault();

            var body = $(this).val();

            var match = body.match(/^\/(.*?)(?: (.*))?$/);
            var args = null;
            if (match) {
                if (match[1] === "msg") {
                    args = match[2].match(/^(.*?) (.*)$/);
                    if (Rooms.participants[args[1]]) {
                        Rooms.connection.send(
                            $msg({
                                to: Rooms.room + "/" + args[1],
                                type: "chat"}).c('body').t(body));
                        Rooms.add_message(
                            "<div class='message private'>" +
                                "@@ &lt;<span class='nick self'>" +
                                Rooms.nickname +
                                "</span>&gt; <span class='body'>" +
                                args[2] + "</span> @@</div>");
                    } else {
                        Rooms.add_message(
                            "<div class='notice error'>" +
                                "Error: User not in room." +
                                "</div>");
                    }
                } else if (match[1] === "me" || match[1] === "action") {
                    Rooms.connection.send(
                        $msg({
                            to: Rooms.room,
                            type: "groupchat"}).c('body')
                            .t('/me ' + match[2]));
                } else if (match[1] === "topic") {
                    Rooms.connection.send(
                        $msg({to: Rooms.room,
                            type: "groupchat"}).c('subject')
                            .text(match[2]));
                } else if (match[1] === "kick") {
                    Rooms.connection.sendIQ(
                        $iq({to: Rooms.room,
                            type: "set"})
                            .c('query', {xmlns: Rooms.NS_MUC + "#admin"})
                            .c('item', {nick: match[2],
                                role: "none"}));
                } else if (match[1] === "ban") {
                    Rooms.connection.sendIQ(
                        $iq({to: Rooms.room,
                            type: "set"})
                            .c('query', {xmlns: Rooms.NS_MUC + "#admin"})
                            .c('item', {jid: Rooms.participants[match[2]],
                                affiliation: "outcast"}));
                } else if (match[1] === "op") {
                    Rooms.connection.sendIQ(
                        $iq({to: Rooms.room,
                            type: "set"})
                            .c('query', {xmlns: Rooms.NS_MUC + "#admin"})
                            .c('item', {jid: Rooms.participants[match[2]],
                                affiliation: "admin"}));
                } else if (match[1] === "deop") {
                    Rooms.connection.sendIQ(
                        $iq({to: Rooms.room,
                            type: "set"})
                            .c('query', {xmlns: Rooms.NS_MUC + "#admin"})
                            .c('item', {jid: Rooms.participants[match[2]],
                                affiliation: "none"}));
                } else {
                    Rooms.add_message(
                        "<div class='notice error'>" +
                            "Error: Command not recognized." +
                            "</div>");
                }
            } else {
                Rooms.connection.send(
                    $msg({
                        to: Rooms.room,
                        type: "groupchat"}).c('body').t(body));
            }

            $(this).val('');
        }
    });

    $(document).bind('room_joined', function () {
        Groupie.joined = true;

        $('#leave').removeAttr('disabled');
        $('#room-name').text(Groupie.room);

        Rooms.add_message("<div class='notice'>*** Room joined.</div>")
    });

    $(document).bind('user_joined', function (ev, nick) {
        Rooms.add_message("<div class='notice'>*** " + nick +
            " joined.</div>");
    });

    $(document).bind('user_left', function (ev, nick) {
        Rooms.add_message("<div class='notice'>*** " + nick +
            " left.</div>");
    });

});
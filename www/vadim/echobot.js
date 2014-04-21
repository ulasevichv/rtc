var BOSH_SERVICE = 'http://192.237.219.76/http-bind';
var connection = null;

function log(msg) 
{
    $('#log').append('<div></div>').append(document.createTextNode(msg));
}

function onConnect(status)
{
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

	connection.addHandler(onMessage, null, 'message', null, null,  null); 
	connection.send($pres().tree());

    }
}

function groupChat() {
    connection.muc.init(connection);
    var d = $pres({"from":"vadim@192.237.219.76","to":"myroom@conference.192.237.219.76/vadim"})
        .c("x",{"xmlns":"http://jabber.org/protocol/muc"});
    connection.send(d.tree());
    connection.muc.createInstantRoom("myroom@conference.192.237.219.76");
    connection.muc.invite("myroom@conference.192.237.219.76",'test@192.237.219.76','her')
}

function onMessage(msg) {
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
    if(message && to){
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
    })
});

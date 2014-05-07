var Rooms = {
    connection: null,
    room: null,
    nickname: null,

    NS_MUC: "http://jabber.org/protocol/muc",

    joined: null,
    participants: null,

    on_presence: function (presence) {
        console.log('rpres');
        var from = $(presence).attr('from');
        var room = Strophe.getBareJidFromJid(from);
        // make sure this presence is for the right room
        console.log(room);
        console.log(Rooms.room);
        console.log(room === Rooms.room);
        if (room === Rooms.room) {
            var nick = Strophe.getResourceFromJid(from);

            if ($(presence).attr('type') === 'error' &&
                !Rooms.joined) {
                // error joining room; reset app
                Rooms.connection.disconnect();
            } else if (!Rooms.participants[nick] &&
                $(presence).attr('type') !== 'unavailable') {
                // add to participant list
                var user_jid = $(presence).find('item').attr('jid');
                Rooms.participants[nick] = user_jid || true;
                $('#participant-list').append('<li>' + nick + '</li>');

                if (Rooms.joined) {
                    $(document).trigger('user_joined', nick);
                }
            } else if (Rooms.participants[nick] &&
                $(presence).attr('type') === 'unavailable') {
                // remove from participants list
                $('#participant-list li').each(function () {
                    if (nick === $(this).text()) {
                        $(this).remove();
                        return false;
                    }
                });

                $(document).trigger('user_left', nick);
            }

            if ($(presence).attr('type') !== 'error' &&
                !Rooms.joined) {
                // check for status 110 to see if it's our own presence
                if ($(presence).find("status[code='110']").length > 0) {
                    // check if server changed our nick
                    if ($(presence).find("status[code='210']").length > 0) {
                        Rooms.nickname = Strophe.getResourceFromJid(from);
                    }

                    // room join complete
                    $(document).trigger("room_joined");
                }
            }
        }

        return true;
    },
    on_invite: function () {
        console.log('catch');
        return true;
    },
    on_public_message: function (message) {
        var from = $(message).attr('from');
        console.log(from);
        var room = Strophe.getBareJidFromJid(from);
//        console.log(room);
//        console.log(message);
        var nick = Strophe.getResourceFromJid(from);

        // make sure message is from the right place
        console.log(Rooms.room);
        console.log(room);
        if (room === Rooms.room) {
            // is message from a user or the room itself?
            var notice = !nick;

            // messages from ourself will be styled differently
            var nick_class = "nick";
            if (nick === Rooms.nickname) {
                nick_class += " self";
            }

            var body = $(message).children('body').text();

            var delayed = $(message).children("delay").length > 0  ||
                $(message).children("x[xmlns='jabber:x:delay']").length > 0;

            // look for room topic change
            var subject = $(message).children('subject').text();
            if (subject) {
                $('#room-topic').text(subject);
            }
            if (!notice) {
                var delay_css = delayed ? " delayed" : "";

                var action = body.match(/\/me (.*)$/);
                if (!action) {
                    Rooms.add_message(
                        "<div class='message" + delay_css + "'>" +
                            "&lt;<span class='" + nick_class + "'>" +
                            nick + "</span>&gt; <span class='body'>" +
                            body + "</span></div>");
                } else {
                    Rooms.add_message(
                        "<div class='message action " + delay_css + "'>" +
                            "* " + nick + " " + action[1] + "</div>");
                }
            } else {
                Rooms.add_message("<div class='notice'>*** " + body +
                    "</div>");
            }
        }

        return true;
    },

    add_message: function (msg) {
        console.log('raddm');
        // detect if we are scrolled all the way down
        var chat = $('#chat').get(0);
        var at_bottom = chat.scrollTop >= chat.scrollHeight -
            chat.clientHeight;

        $('#chat').append(msg);

        // if we were at the bottom, keep us at the bottom
        if (at_bottom) {
            chat.scrollTop = chat.scrollHeight;
        }
    },

    on_private_message: function (message) {
        console.log('rprm');
        var from = $(message).attr('from');
        var room = Strophe.getBareJidFromJid(from);
        var nick = Strophe.getResourceFromJid(from);

        // make sure this message is from the correct room
        if (room === Rooms.room) {
            var body = $(message).children('body').text();
            Rooms.add_message("<div class='message private'>" +
                "@@ &lt;<span class='nick'>" +
                nick + "</span>&gt; <span class='body'>" +
                body + "</span> @@</div>");

        }

        return true;
    },
    on_message: function (message) {
        console.log('mmmmmmmmmm');


        return true;
    }
};
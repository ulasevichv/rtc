<?php
Yii::app()->clientScript->registerScript(uniqid('chat_rooms_js'), "
	var ChatRooms = {
        connection : null,
		roomsList : null,

        init: function()
        {

            console.log('Rooms init');

            //Init MUC plugin and get public Rooms list
            Chat.conn.muc.init(Chat.conn);
            ChatRooms.getPublicRooms()
        },
		getPublicRooms : function()
		{
            Chat.conn.muc.listRooms('conference.".Yii::app()->params->xmppServerIP."', ChatRooms.showRoomsList, ChatRooms.showRoomsListError);
            return true;
		},
		showRoomsList: function(param1) {
		    var feed = [];
		    var rooms = param1.getElementsByTagName('query');
		    console.log('showRoomsList');
		    feed.push('<div class=\"heading1\">".Yii::t('general','Group Chats')."</div>');
		    $.each(rooms[0].childNodes, function( index, value ) {
		         feed.push('<div class=\"GroupRoom\" roomId=\"' + value.getAttribute('jid') + '\">');
		         feed.push('<div class=\"icon\"></div>');
		         feed.push(value.getAttribute('name'));
		         feed.push('</div>');
            });
            $('#group_rooms').html(feed.join(''));
		},
		showRoomsListError: function(err) {
		    console.log('showRoomsListError');
		    console.log(err);
		}

	}
", CClientScript::POS_HEAD);
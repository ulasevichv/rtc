var OTvideo = OTvideo || {};

OTvideo.apiKey = null;
OTvideo.sessionId = null;
OTvideo.token = null;
OTvideo.publisher = null;
OTvideo.session = null;
OTvideo.subscribers = {};
OTvideo.isInCall = false;
OTvideo.myDiv = '';
OTvideo.myDivVideo = '';
OTvideo.smallwidth = 100;
OTvideo.smallheight = 67;
OTvideo.bigwidth = 300;
OTvideo.bigheight = 200;
OTvideo.currentVideo = null;
OTvideo.event = null;
OTvideo.expandedMode = false;

OTvideo.init = function() {
    OTvideo.session = TB.initSession(OTvideo.apiKey, OTvideo.sessionId);
    OTvideo.myDivVideo = OTvideo.myDiv + ' .video';
    $(OTvideo.myDivVideo).append('<div id=\"bigvideo\"></div>');
    $(OTvideo.myDivVideo).append('<div id=\"myvideo\" class=\"active videoItem\"></div>');
    $(OTvideo.myDivVideo).show(400);

    OTvideo.session.connect(OTvideo.token, function(error) {
        console.log('OTvideo.session.connect');
        var publisherProperties = {width: OTvideo.bigwidth, height:OTvideo.bigheight, name:Chat.currentUser.fullName, controls: true};
        OTvideo.publisher = OT.initPublisher('myvideo',publisherProperties);
        OTvideo.session.publish(OTvideo.publisher);
        return true;
    });
    OTvideo.session.on('streamCreated', function(event) {
        console.log('OTvideo.session.streamCreated');
        var id = 'video-' + event.stream.connection.connectionId;
        var videoDiv = null;
        if (OTvideo.expandedMode) {
            videoDiv = $('#video-expanded .video-expanded-videoContainer .video');
        } else {
            videoDiv = $(OTvideo.myDivVideo);
        }
        videoDiv.append('<div class=\"other-video videoItem\" style=\"float:left;\" id=\"' + id + '\"></div>');
        OTvideo.session.subscribe(event.stream, id, {style: {'nameDisplayMode': 'on'}, width: OTvideo.smallwidth, height: OTvideo.smallheight});
        return true;
    });
    OTvideo.session.on("sessionConnected", function(event) {
        console.log('sessionConnected');
        OTvideo.event = event;
        OTvideo.isInCall = true;
        OTvideo.showControls();
    });
    OTvideo.session.on("sessionDisconnected", function(event) {
        OTvideo.isInCall = false;
        $(OTvideo.myDiv + ' .video').hide(0);
        OTvideo.hideControls();
        ChatGUI.resizeChatTextDiv();
        Chat.changeStatus('online',"Online");

    });
    OTvideo.session.on("connectionDestroyed", function(event) {
        OTvideo.session.disconnect();
        $(OTvideo.myDiv + ' .video-toggle').hide(0);
        $(OTvideo.myDiv + ' .video').hide(0);
        ChatGUI.resizeChatTextDiv();
        Chat.changeStatus('online',"Online");
    });

    $(document).on('click','.videoItem', function() {
        OTvideo.focus(this.id)
    });

    $(document).on('click','.OT_mute', function() {
        return false;
    });

    $( window ).resize(function() {
        if (OTvideo.expandedMode) {
            OTvideo.expandedVideoResize();
        }
        return true;
    });
}
OTvideo.showControls = function() {
    $('#userPanel #btnStartVideoCall').hide(0);
    $('#userPanel #btnEndCall').show(0);
    $(OTvideo.myDiv + ' .video-toggle').show(0);
    $('#userPanel #btnExpandVideo').show(0);
}
OTvideo.hideControls = function() {
    $(OTvideo.myDiv + ' .video-toggle').hide(0);
    $('#userPanel #btnStartVideoCall').show(0);
    $('#userPanel #btnEndCall').hide(0);
    $('#userPanel #btnExpandVideo').hide(0);
}
OTvideo.focus = function(video_id) {
    OTvideo.currentVideo = video_id;
    var active = $('.video .active').get(0);
    var focused = $('#'+video_id);

    if (focused.size()) {
//        $('.other-video, #myvideo').addClass('hidden');
        focused.show();
        if (active) {
            active.style.width = OTvideo.smallwidth+'px';
            active.style.height = OTvideo.smallheight+'px';
            active.style.left = 0+'px';
        }
        $('.other-video').removeClass('active');
        $('#myvideo').removeClass('active');
        focused.prependTo('#bigvideo');

        focused.addClass('active');

        focused.css('width', OTvideo.bigwidth+'px');
        focused.css('height', OTvideo.bigheight+'px');

        OTvideo.expandedVideoResize();
    }
}
OTvideo.expand = function() {
    $(OTvideo.myDiv + ' .video').appendTo('#video-expanded .video-expanded-videoContainer');
    $('#video-expanded').show();
    $('.video').show();
    OTvideo.expandedMode = true;
    OTvideo.expandedVideoResize();
    return true;
}
OTvideo.collapse = function() {
    var active = $('.video .active');

    active.css('width', OTvideo.bigwidth+'px');
    active.css('height', OTvideo.bigheight+'px');
    active.css("left",'0px');
    $('#video-expanded .video-expanded-videoContainer .video').prependTo(OTvideo.myDiv);
    $('#video-expanded').hide();
    OTvideo.expandedMode = false;
    return true;
}
OTvideo.expandedVideoResize = function () {
    var height = $('#video-expanded').outerHeight() - $('#video-expanded .video-expanded-buttons').outerHeight() - 150;
    $('#video-expanded .active').css('height',height + 'px');
    $('#video-expanded .active').css('width',height/3*4 + 'px');
    OTvideo.centerDiv();
}
OTvideo.centerDiv = function() {
    $('#video-expanded .active').css("left", ($('#video-expanded').innerWidth() - $('#video-expanded .active').outerWidth())/2 + "px");
}
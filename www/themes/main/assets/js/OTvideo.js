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

OTvideo.init = function() {
    OTvideo.session = TB.initSession(OTvideo.apiKey, OTvideo.sessionId);
    OTvideo.myDivVideo = OTvideo.myDiv + ' .video';
    $(OTvideo.myDivVideo).append('<div id=\"myvideo\"></div>');
    $(OTvideo.myDivVideo).show(400);

    OTvideo.session.connect(OTvideo.token, function(error) {
        console.log('OTvideo.session.connect');
        OTvideo.publisher = OT.initPublisher('myvideo');
        OTvideo.session.publish(OTvideo.publisher);
        return true;
    });
    OTvideo.session.on('streamCreated', function(event) {
        console.log('OTvideo.session.streamCreated');
        var id = 'video-' + event.stream.connection.connectionId;
        $(OTvideo.myDivVideo).append('<div class=\"other-video\" style=\"float:left;\" id=\"' + id + '\"></div>');
        OTvideo.session.subscribe(event.stream, id);
        return true;
    });
    OTvideo.session.on("sessionConnected", function(event) {
        OTvideo.isInCall = true;
        $('#userPanel #btnStartVideoCall').hide(0);
        $('#userPanel #btnEndCall').show(0);
        $(OTvideo.myDiv + ' .video-toggle').show(0);

    });
    OTvideo.session.on("sessionDisconnected", function(event) {
        OTvideo.isInCall = false;
        $('#userPanel #btnEndCall').hide(0);
        $('#userPanel #btnEndCall').hide(0);
        $(OTvideo.myDiv + ' .video-toggle').hide(0);
    });
    OTvideo.session.on("connectionDestroyed", function(event) {
        OTvideo.session.disconnect();
    });
}
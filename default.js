var READY_STATE_UNINITIALIZED=0;
var READY_STATE_LOADING=1;
var READY_STATE_LOADED=2;
var READY_STATE_INTERACTIVE=3;
var READY_STATE_COMPLETE=4;

var request = createRequest();
var voteRequest = createRequest();
var addRequest = createRequest();
var playerControlRequest = createRequest();

if( request == null)
{
    alert("XMLHttpRequest object cannot be created. Application cannot run.");
}

function createRequest(){
    var req = null;

    // Non-Microsoft browsers
    try {
        req = new XMLHttpRequest();
    } catch(ex) {
        // Microsoft browsers
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch(ex1) {
            // Other Microsoft versions
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch(ex2) {
                req = null;
            }
        }
    }

    return req;
}

function playerControl() {
    playerControlRequest = createRequest();
    if(playerControlRequest == null) {
        return false;
    }
    playerControlRequest.onreadystatechange = playerControlProcessRequestChange;
    playerControlRequest.open("GET", 'playerControl.php', true);
    playerControlRequest.send(null);
    
    return false;
}

function add(url) {
    addRequest = createRequest();
    if(addRequest == null) {
        return false;
    }
    addRequest.onreadystatechange = addProcessRequestChange;
    addRequest.open("GET", 'add.php?url=' + url.value, true);
    addRequest.send(null);

    url.value = 'DATA';
    loadDocument('playlist.php');
    return false;
}

function vote(videoid) {
    voteRequest = createRequest();
    if(voteRequest == null) {
        return;
    }
    
    voteRequest.onreadystatechange = voteProcessRequestChange;
    voteRequest.open("GET", 'vote.php?videoid=' + videoid, true);
    voteRequest.send(null);
    loadDocument('playlist.php');
}

function voteProcessRequestChange() {
    if(voteRequest == null) {
        return;
    }

    var ready = voteRequest.readyState;

    if(ready == READY_STATE_COMPLETE) {
        //parse(voteRequest.responseText);
    }

}

function playerControlProcessRequestChange() {
    if(playerControlRequest == null) {
        return;
    }

    var ready = playerControlRequest.readyState;
    //alert(addRequest.responseText);
    if(ready == READY_STATE_COMPLETE) {
        //alert(playerControlRequest.responseText);
        eval(playerControlRequest.responseText);
    }

}

function addProcessRequestChange() {
    if(addRequest == null) {
        return;
    }

    var ready = addRequest.readyState;
    //alert(addRequest.responseText);
    if(ready == READY_STATE_COMPLETE) {
        //alert(addRequest.responseText);
    }

}

function start() {
    startPlayer();
    loadDocument('playlist.php');
    setInterval("loadDocument('playlist.php')", 2000);
}

function startPlayer() {
    $f("player", {src: "flowplayer-3.1.3.swf", wmode: 'opaque' }, {
        clip: {
            autoPlay: true,
            autoBuffering: true,
            scaling: 'fit'
        },
        plugins: {
            controls: null
        },
        canvas:  {
            // configure background properties
            background: '#000000',

            // remove default canvas gradient
            backgroundGradient: 'none'
        },
        onLoad: function() {
            this.setVolume(0);
        }
    });
    setInterval("playerControl();", 2000);
}

function loadDocument(url) {
    
    //document.getElementById("loading").style.display = "block";
    request = createRequest();
    if(request == null) {
        return;
    }

    request.onreadystatechange = processRequestChange;
    request.open("GET", url, true);
    request.send(null);
}

function processRequestChange() {
    if(request == null) {
        return;
    }

    var ready = request.readyState;
    
    if(ready == READY_STATE_COMPLETE) {
        parse(request.responseText);
    }

}

function parse(response) {
    if(response == null) {
        return;
    }
    
    document.getElementById("playlist").innerHTML = response;
}

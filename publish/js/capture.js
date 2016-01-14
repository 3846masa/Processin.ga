window.URL = window.URL || window.webkitURL || window.mozURL || window.msURL;
navigator.getUserMedia =
  navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
if (!!navigator.getUserMedia && !!MediaStreamTrack && !!MediaStreamTrack.getSources) {
  MediaStreamTrack.getSources(function(sources) {
    MediaStreamTrack.sources = [];
    sources.forEach(function(source){
      if (source.kind === "video") MediaStreamTrack.sources.push(source.id);
    });
  });
}

var Capture = function() {
  this.sourceImg = document.createElement('video');
  this.sourceImg.style.display = "none";
  document.querySelector('body').appendChild(this.sourceImg);
  this.width = 0; this.height = 0; this.camName = "";
  this.stream = null;
  if (arguments.length >= 3) {
    this.width = arguments[1]; this.height = arguments[2];
    if (arguments.length >= 4 && !isFinite(arguments[3])) {
      this.camName = arguments[3];
    }
  } else if (arguments.length === 2) {
    this.camName = arguments[1];
  }
};
Capture.prototype = {
  read: function(){
    this.sourceImg.width = this.width = this.width || this.sourceImg.videoWidth;
    this.sourceImg.height = this.height = this.height || this.sourceImg.videoHeight;
  },
  available: function(){
    if (!!this.sourceImg && !this.sourceImg.paused) {
      return true;
    }
    return false;
  },
  start: function(){
    var mine = this;
    navigator.getUserMedia({
      video: (!MediaStreamTrack.getSources) ? true : {
        optional: [{sourceId: this.camName}]
      }
    }, function(stream){
      mine.stream = stream;
      mine.sourceImg.setAttribute("src", window.URL.createObjectURL(stream));
      mine.sourceImg.addEventListener('canplay', function(){
        mine.sourceImg.play();
        mine.width = mine.width || mine.sourceImg.videoWidth;
        mine.height = mine.height || mine.sourceImg.videoHeight;
      });
      mine.sourceImg.play();
      mine.width = mine.width || mine.sourceImg.videoWidth;
      mine.height = mine.height || mine.sourceImg.videoHeight;
    }, function(){});
  },
  stop: function(){
    var oldVideo = this.sourceImg;
    this.sourceImg = document.createElement('video');
    this.sourceImg.style.display = "none";
    document.querySelector('body').appendChild(this.sourceImg);
    oldVideo.remove();
    this.stream.stop();
    this.stream = null;
  }
};
Capture.list = function() {
  if (!navigator.getUserMedia) {
    return [];
  } else {
    if (!MediaStreamTrack || !MediaStreamTrack.sources) {
      return ["cam1"];
    } else {
      return MediaStreamTrack.sources;
    }
  }
};

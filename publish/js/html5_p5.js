(function(){
  window.vibrate = function () {
    var vibrate = navigator.vibrate || navigator.mozVibrate ||
      navigator.webkitVibrate || function(){console.log("Vibrate is not supported.\n");};
    vibrate.apply(navigator, arguments);
  };

  window.accelerationX = window.accelerationY = window.accelerationZ = 0;
  window.gravityX = window.gravityY = window.gravityZ = 0;

  function handleMotionEvent(ev) {
    ev = ev.originalEvent;
    window.accelerationX = ev.acceleration.x;
    window.accelerationY = ev.acceleration.y;
    window.accelerationZ = ev.acceleration.z;

    window.gravityX = ev.accelerationIncludingGravity.x;
    window.gravityY = ev.accelerationIncludingGravity.y;
    window.gravityZ = ev.accelerationIncludingGravity.z;
  }

  $(window).bind("devicemotion", handleMotionEvent);

  window.deviceRotateX = window.deviceRotateY = window.deviceRotateZ = 0;
  function handleOrientationEvent(ev) {
    ev = ev.originalEvent;
    window.deviceRotateX = ev.bate;
    window.deviceRotateY = ev.gamma;
    window.deviceRotateZ = ev.alpha;
  }

  $(window).bind("deviceorientation", handleOrientationEvent);
})();

//APIを格納
window.URL = window.URL || window.webkitURL || window.mozURL || window.msURL;
//window.URLのAPIをすべてwindow.URLに統一
navigator.getUserMedia =
  navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;

$(function(){
  var cnt = 0;
  var localStream, scanInterval;
  var cameraData = [];
  //setting
  var mode = "debug";
  var video = $("#myVideo");
  var canvas = $("#qr-canvas");
  var context = canvas[0].getContext("2d");

  // スクロールの禁止
  $(window).bind('touchmove', function(e){
    e.preventDefault();
    return false;
  });

  // ポップアップの初期化
  var startup = $('[data-remodal-id=startup]').remodal({
    hashTracking: false,
    closeOnAnyClick: false,
    closeOnEscape: false,
    closeOnCancel: false
  });
  var notcam = $('[data-remodal-id=notcam]').remodal({
    hashTracking: false,
    closeOnAnyClick: false,
    closeOnEscape: false
  });
  $('.remodal-cancel', startup.$body).bind('click', function() {location.reload(false);});

  // カメラが対応していない場合
  if (!navigator.getUserMedia) {
    notcam.open();
    notcam.$wrapper.css({display: "block"});
  }

  //qrcode scaner
  function scanQR(){
    // ビデオのサイズとCanvasのサイズを同じにする
    canvas.attr({width: video.width(), height: video.height()});
    context.drawImage(video[0], 0, 0, video.width(), video.height());
    try {
      qrcode.decode();
    } catch (e) {
    }
  }

  qrcode.callback = function(result) {
    regexp = new RegExp(/(debug|viewer)/);
    if (result.match(regexp)) {
      location.href = result;
      //読み込みに成功したら再読み込みを防止する
      clearInterval(scanInterval);
    }
  };

  var successCallback = function(stream) {
    video[0].src = window.URL.createObjectURL(stream);
    localStream = stream;
    video[0].play();
    scanInterval = setInterval(scanQR, 500);

    (function closeRemodal() {
      if (startup.busy) setTimeout(closeRemodal, 100);
      else startup.close();
    })();
  };
  var videoError = function(error){};

  //android用のプログラム
  function scanStart() {
    if (MediaStreamTrack.getSources) {
      // カメラの情報を取得
      MediaStreamTrack.getSources(function(sources) {
        sources.forEach(function(source) {
          if (source.kind === "video") cameraData.push(source.id);
        });
        if (cameraData.length === 0) alert("カメラが見つかりません");
        setCamera();
      });
    } else {
      setCamera();
    }

    $("#changeButton").bind("click",function(){
      setCamera();
    });
  }

  //カメラを取得・切り替える
  function setCamera(){
    if (cameraData.length > 0) {
      //カメラを順番に切り替える
      cnt = (cnt + 1) % cameraData.length;
    }
    //カメラ再生中の場合は切り替えのため、いったん停止する
    // if (localStream) localStream.stop();
    if (scanInterval) {
      clearInterval(scanInterval);
      scanInterval = null;
    }

    startup.open();
    startup.$wrapper.css({display: "block"});
    navigator.getUserMedia({
      video: (!MediaStreamTrack.getSources) ? true : {
        optional: [{sourceId: cameraData[cnt]}]
      }
    }, successCallback, videoError);
  }

  //iPhone用のプログラム
  function iOSFunc(){
    setTimeout(function(){
      // iOSのバージョン取得
      var version = navigator.userAgent.match(/(?:iPhone|iPad)\s*OS\s*([\d_]+)/)[1].replace(/_/g,'');
      version = ("" + version + "00").substr(0, 3);
      // iOS8のみ動作
      if (version < 800) return;

      (function launchQRReader(cnt) {
        // cntを初期化
        if (!isFinite(cnt)) cnt = 0;
        else cnt = parseInt(cnt);
        // QRコードアプリのURLスキーム
        var schemeList = [
          "zxing:", "scan:", "iconit:",
          "qr:", "aa-qrcam:", "simpleqrreader:",
          "OtousanQRCodeReader:",
          "qrafter:", "QuickMark:"
        ];
        if (schemeList.length <= cnt) {
          return;
        }
        var scheme = schemeList[cnt];

        // iframeでURLスキームにアクセス
        var iframe = $('<iframe>').css({display: "none"});
        var start = new Date();
        iframe.attr({src: scheme});
        $('body').append(iframe);
        setTimeout(function(){
          // アプリが起動しなければ，次のアプリへ
          if (new Date() - start <= 500 + 30) launchQRReader(cnt+1);
        }, 500);
      })();
    }, 3000);
  }

  //デバイスによる振り分け
  var agent = navigator.userAgent;
  console.log(agent);
  if (agent.match(/(iPhone|iPad)/i)){
    iOSFunc();
  } else if(agent.match(/(Android|mobile|tablet)/i)){
    scanStart();
  } else{
    location.href = "/";
  }
});

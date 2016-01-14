<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8'>
    <title>Processin.ga Debuger</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="/imgs/favicon.ico" type="image/vnd.microsoft.icon" />
    <link rel="icon" href="/imgs/favicon.ico" type="image/vnd.microsoft.icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

    <script src="/js/capture.js?_=<?php echo time(); ?>"></script>
    <script src="/js/jquery-2.1.1.min.js"></script>
    <script src="/js/FileSaver.min.js"></script>
    <script src="/js/canvas-toBlob.js"></script>
    <script src="/js/html5_p5.js?_=<?php echo time(); ?>"></script>
    <script src="/js/processing.js?_=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="/css/debug.css">

    <script>
      var conn = null;
      var id = <?php
        echo json_encode(isset($_REQUEST['id']) ? $_REQUEST['id'] : "", JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
      ?>;
      var port = "443";

      var print = console;

      var sendConsole = function(msg, type) {
        conn.send(JSON.stringify({
          id: id,
          action: 'post',
          data: {
            type: type,
            message: msg
          }
        }));

        if (type === 'error') {
          showError(msg);
        }
      };

      var showError = function(msg) {
        var errLog = $('textarea#error');
        errLog.scrollTop(errLog[0].scrollHeight - errLog.height());
        errLog.val(errLog.val() + msg + "\n").show();
      };

      var printErr = print.error;
      print.error = function(msg) {
        showError(msg);
      };

      var send = {
        console: sendConsole,
        log: function(msg) {this.console(msg, 'log')},
        error: function(msg) {this.console(msg, 'error')},
      };
      console = send;

      var sendInfo = function() {
        var docWindow = document.documentElement;
        conn.send(JSON.stringify({
          id: id,
          action: 'post',
          data: {
            type: 'info',
            message: {
              isDevice: (window === parent),
              width: docWindow.clientWidth,
              height: docWindow.clientHeight
            }
          }
        }));
      };

      var connectServer = function() {
        conn = new WebSocket('wss://' + location.hostname + ':' + port + '/chat');

        $(conn).bind('message', function(e) {
          e = e.originalEvent;
          var json = JSON.parse(e.data);
          if (json.data == null) return;
          var data = json.data;

          if (data.type == "request") {
            if (data.message == "info") {
              sendInfo();
            }
          } else if (data.type === "code" && window === parent) {
            runCode(data.message);
          }
          // console.log(e.data);
        });

        $(conn).bind('error', function(e) {
          e = e.originalEvent;
          console = print;
          console.error("Connection Error.");
          conn = null;
        });
      };

      var runCode = function(code, type) {
        if (code != null && code.length > 0) {
          $('canvas').css({"background-color": "#CCCCCC"});
        }
        if (type === 'init') {
          try {
            var processing = new Processing($('canvas')[0], code);
          } catch (e) {
            printStackTrace(e);
          }
        } else {
          var url = location.pathname;
          history.replaceState(null, null, "/reader");

          var frm = $('<form>').attr({
            action: url,
            method: 'POST',
          }).css({display: 'none'});
          var idInput = $('<input>').attr({name: 'id'})
            .css({display: 'none'}).val(id);
          var codeText = $("<textarea>").attr({name: 'code'})
            .css({display: 'none'}).val(code);
          frm.append(idInput).append(codeText);
          $('body').append(frm);

          frm.submit();
          frm.remove();
        }
      };

      connectServer();

      function printStackTrace(e) {
        if (e.stack) {
          console.error(e.stack);
        }
        else console.error(e.message, e);
      };

      $(function(){
        $(conn).bind('open', function(e) {
          e = e.originalEvent;
          if (id === "") id = "test";
          conn.send(JSON.stringify({
            id: id,
            action: 'join'
          }));

          sendInfo();
          var code = <?php echo json_encode($_POST['code'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
          runCode(code, 'init');
        });

        $('.menu-icon').addClass((window === parent) ? "md-developer-mode" : "md-play-arrow");
      });

      var docWindow = document.documentElement;
      var displayWidth = docWindow.clientWidth;
      var displayHeight = docWindow.clientHeight;
    </script>
  </head>
  <body>
    <div class="wrap">
      <div class="info">
        <div>
          <span>エディタの<i class="menu-icon"></i>ボタンを押すと，</span><wbr>
            <span>この画面上で実行されます</span>
          </div>
        </div>
      </div>
    <p id="logo"><img src="/imgs/logo.png"></p>
    <canvas></canvas>
    <textarea id="error" readonly></textarea>
  </body>
</html>

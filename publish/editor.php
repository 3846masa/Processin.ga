<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8'/>
    <title>Processin.ga Editor</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="/imgs/favicon.ico" type="image/vnd.microsoft.icon" />
    <link rel="icon" href="/imgs/favicon.ico" type="image/vnd.microsoft.icon" />

    <script src="/js/jquery-2.1.1.min.js"></script>
    <script src="/syntaxHighlighter/scripts/shCore.js"></script>
    <script src="/syntaxHighlighter/scripts/shBrushProcessing.js"></script>
    <script src="/js/jquery.remodal.js"></script>
    <link rel="stylesheet" href="/syntaxHighlighter/styles/shCore.css">
    <link rel="stylesheet" href="/syntaxHighlighter/styles/shProcessing2Theme.css">
    <link rel="stylesheet" href="/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="/css/jquery.remodal.css">

    <link rel="stylesheet" href="/css/editor.css">
    <script>
      var id = <?php
        echo json_encode(
          isset($_REQUEST['id']) ? $_REQUEST['id'] : "",
          JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
      ?>;
    </script>
    <script src="/js/editor.js?_=<?php echo time(); ?>"></script>
  </head>
  <body>
    <div class="remodal-bg">

    <div id='main'>
      <div class='menu'>
        <i title="Run" class="md-play-arrow menu-icon run-button"></i>
        <i title="Run with Device" class="md-developer-mode menu-icon run-button"></i>
        <i title="Stop" class="md-stop menu-icon"></i>
        <i title="Save" class="md-save menu-icon square"></i>
        <!-- <i title="Upload" class="md-file-upload menu-icon square"></i>
        <i title="Export" class="md-file-download menu-icon square"></i>
        <i title="Publish" class="md-public menu-icon square"></i> -->
        <i title="QR" class="md-settings menu-icon square"></i>
        <a href="/help.html" target="_blank"><i title="Help" class="md-help menu-icon square"></i></a>

        <i class="md-info-outline"></i>
        <select name="screen">
          <option value=",">Connected Device</option>
          <option value="320,480" selected>iPhone3G,3GS</option>
          <option value="320,480">iPhone4,4S</option>
          <option value="320,568">iPhone5,5s,5c</option>
          <option value="375,667">iPhone6</option>
          <option value="414,736">iPhone6Plus</option>
          <option value="custom">Custom</option>
        </select>
        <input name="width"><span>x</span><input name="height">
      </div>
      <div class='log' contenteditable></div>
      <div class='logBG'></div>
      <div class='edit'>
        <textarea class="editor"></textarea>
        <div class="position"></div>
        <pre class="brush: pde; gutter: false; toolbar: false;">
          // Please write a code !
        </pre>
      </div>
    </div>

    <div id="preview">
      <iframe name="preview" src="/debug" scrolling="no"></iframe>
    </div>

    <script>
      SyntaxHighlighter.highlight();
    </script>

    </div>

    <div data-remodal-id="startup">
      <h1>Welcome!</h1>
      <p>
        スマートフォンから&nbsp;
        <strong>https://processin.ga/reader</strong>&nbsp;にアクセスして，<br>
        下のQRコードを読み込んでください．<br>
      </p>
      <p><img class="qrcode"></p>
      <a class="remodal-cancel" href="#">このまま始める</a>
    </div>
    <div data-remodal-id="save">
      <form>
        <h1>Save</h1>
        <h2>Title</h2>
        <p>
          <input type="text" name="title" required>
        </p>
        <h2>Username</h2>
        <p>
          <input type="text" name="username" required>
        </p>
        <h2>Password</h2>
        <p>
          <p><small>再編集のときに必要になります</small></p>
          <input type="password" name="secretkey" required>
        </p>
        <h2>Publish</h2>
        <p>
          <p><small>非公開状態では実行にもパスワードが必要になります</small></p>
          <input type="checkbox" id="savePublish" name="published">
          <label for="savePublish">公開状態にする</label>
        </p>
        <button type="submit">送信</button>
        <a class="remodal-confirm" href="#">保存</a>
        <a class="remodal-cancel" href="#">戻る</a>
      </form>
    </div>
    <div data-remodal-id="load">
      <form>
        <h1>Load</h1>
        <h2>Password</h2>
        <p>
          <input type="password" name="secretkey" required>
        </p>
        <p class="error"></p>
        <button type="submit">送信</button>
        <a class="remodal-confirm" href="#">読み込み</a>
      </form>
    </div>
    <div data-remodal-id="message">
      <h1 class="title">Welcome!</h1>
      <p class="message">Text Message</p>
      <a class="remodal-confirm" href="#">了解</a>
    </div>
  </body>
</html>

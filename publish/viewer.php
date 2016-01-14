<?php
/* 必要な引数を確認 */
if (!isset($_GET["id"])) {
  header("Location: /");
}
/* SQLを実行 */
try {
  $pdo = new PDO("sqlite:api/CMP.sqlite");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  $st = $pdo->prepare("SELECT title, username, code FROM pdeStore WHERE id = :id AND published = 1");
  $st->bindValue(":id", $_GET["id"], PDO::PARAM_STR);
  $st->execute();
  $data = $st->fetch(PDO::FETCH_ASSOC);
  if (!$data) header("Location: /");
} catch (PDOException $err) {
  unset($pdo);
  header("Location: /");
}
$code = $data["code"];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8'>
    <title><?php
      echo htmlspecialchars(
        $data["title"]."/".$data["username"]." - Processin.ga",
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5
      );
    ?></title>
    <link rel="shortcut icon" href="/imgs/favicon.ico" type="image/vnd.microsoft.icon" />
    <link rel="icon" href="/imgs/favicon.ico" type="image/vnd.microsoft.icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

    <meta property="og:title" content="<?php
      echo htmlspecialchars(
        $data["title"]."/".$data["username"]." - Processin.ga",
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5
      );
    ?>">
    <meta property="og:type" content="article">
    <meta property="og:description" content="Processin.ga - Processing.js editor on web">
    <meta property="og:url" content="<?php
      echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . "/viewer/" . $_GET["id"];
    ?>">
    <meta property="og:image" content="<?php
      echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . "/api/qr/" . $_GET["id"];
    ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@processinga">
    <meta name="twitter:title" content="<?php
      echo htmlspecialchars(
        $data["title"]."/".$data["username"]." - Processin.ga",
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5
      );
    ?>">
    <meta name="twitter:description" content="Processin.ga - Processing.js editor on web">
    <meta name="twitter:image:src" content="<?php
      echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . "/api/qr/" . $_GET["id"];
    ?>">

    <script src="/js/capture.js?_=<?php echo time(); ?>"></script>
    <script src="/js/jquery-2.1.1.min.js"></script>
    <script src="/js/jquery.remodal.js"></script>
    <script src="/js/FileSaver.min.js"></script>
    <script src="/js/canvas-toBlob.js"></script>
    <script src="/js/html5_p5.js?_=<?php echo time(); ?>"></script>
    <script src="/js/processing.js?_=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="/css/debug.css">
    <link rel="stylesheet" href="/css/jquery.remodal.css">

    <script>
      var conn = null;
      var id = <?php
        echo json_encode(isset($_REQUEST['id']) ? $_REQUEST['id'] : "", JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
      ?>;

      var showError = function(msg) {
        var errLog = $('textarea#error');
        errLog.scrollTop(errLog[0].scrollHeight - errLog.height());
        errLog.val(errLog.val() + msg + "\n").show();
      };

      console.error = showError;

      var runCode = function(code, type) {
        $('canvas').css({"background-color": "#CCCCCC"});
        try {
          var processing = new Processing($('canvas')[0], code);
        } catch (e) {
          printStackTrace(e);
        }
      };

      function printStackTrace(e) {
        if (e.stack) {
          console.error(e.stack);
        }
        else console.error(e.message, e);
      };

      $(function(){
        if (navigator.userAgent.match(/(iPhone|iPad|Android|Mobile|Tablet)/i)) {
          var code = <?php echo json_encode($code, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
          setTimeout(function(){runCode(code, 'init');}, 500);
        } else {
          var startup = $('[data-remodal-id=startup]').remodal({
            hashTracking: false,
            closeOnAnyClick: false,
            closeOnEscape: false
          });
          $('.qrcode').attr({src: "/api/qr/"+id});
          startup.open();
        }
      });

      var docWindow = document.documentElement;
      var displayWidth = docWindow.clientWidth;
      var displayHeight = docWindow.clientHeight;
    </script>
  </head>
  <body>
    <div class="remodal-bg">
      <canvas></canvas>
      <textarea id="error" readonly></textarea>
    </div>

    <div data-remodal-id="startup">
      <h1>Welcome!</h1>
      <p>
        ChromeまたはFirefoxをインストールした<br>
        スマートフォンから&nbsp;
        <strong>https://processin.ga/reader</strong>&nbsp;にアクセスして，<br>
        下のQRコードを読み込んでください．<br>
      </p>
      <p><img class="qrcode"></p>
    </div>
  </body>
</html>

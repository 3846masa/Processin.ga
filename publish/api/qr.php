<?php

// もし，qrが設定されてなければ，エラーを返す
if(isset($_GET["qr"])){
  $unitime = $_GET["qr"];
} else {
  header('HTTP', true, 400);
  exit();
}

// QRコードのモード設定
if (isset($_GET["mode"]) && preg_match("/^debug$/i", $_GET["mode"])) {
  $mode = "debug";
} else {
  $mode = "viewer";
}

// ライブラリの読み込み
include('./phpqrcode/qrlib.php');
// QRコードの中を設定
$codeContents = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . "/${mode}/${unitime}";

ob_start(); //バッファリング開始
QRcode::png($codeContents, FALSE, QR_ECLEVEL_H, 7);
$data = ob_get_contents(); //出力されるはずだったデータを取得
ob_end_clean();

// 画像の読み込み
$qrImg = imagecreatefromstring($data);
$logoImg = imagecreatefrompng("QRup.png");
$backImg = imagecreatefromjpeg("background.jpg");
$resultImg = imagecreatetruecolor(imagesx($qrImg), imagesy($qrImg));

// 丸みをつける
$matrix = array(array(1, 1, 1), array(1, 1, 1), array(1, 1, 1));
imageconvolution($qrImg, $matrix, 9, 0);

// フルカラー画像にする
$tmp = imagecreatetruecolor(imagesx($qrImg), imagesy($qrImg));
imagecopy($tmp, $qrImg, 0, 0, 0, 0, imagesx($qrImg), imagesy($qrImg));
imagedestroy($qrImg);
$qrImg = $tmp;
// 黒を透過する
imagecolortransparent($qrImg, imagecolorallocate($qrImg, 0, 0, 0));

// 背景を設定
imagecopyresampled(
  $resultImg, $backImg,
  0, 0,
  0, 0,
  imagesx($resultImg), imagesy($resultImg),
  imagesx($backImg), imagesy($backImg)
);
// QRコードを合成
imagecopy($resultImg, $qrImg, 0, 0, 0, 0, imagesx($qrImg), imagesy($qrImg));

// ロゴ画像を合成
$logoScale = 0.25;
imagecopyresampled(
  $resultImg, $logoImg,
  (imagesx($resultImg)*(1-$logoScale))/2, (imagesy($resultImg)*(1-$logoScale))/2,
  0, 0,
  imagesx($resultImg)*$logoScale, imagesy($resultImg)*$logoScale,
  imagesx($logoImg), imagesy($logoImg)
);

// 出力
header('Content-Type: image/png');
imagepng($resultImg);

// メモリ解放
imagedestroy($qrImg);
imagedestroy($logoImg);
imagedestroy($backImg);
imagedestroy($resultImg);
exit();

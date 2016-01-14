Processin.ga
============

http://processin.ga

先端メディアサイエンス学科「コンテンツメディアプログラミング実習」課題．

## Processin.gaについて
Processin.gaは，Processing.jsを利用したアプリケーション開発環境です．

Processing.jsについては，http://processingjs.org/reference/ をご覧ください．

## Processin.gaで追加された機能
Processin.gaでは，Processing.jsにない機能をいくつか搭載しています．

### save()
http://processingjs.org/reference/save_/ の仕様を変更しました．

従来は別ウィンドウに画像が表示されるだけでしたが，命名したファイル名で保存されるようになりました．

### Capture Class
https://processing.org/reference/libraries/video/Capture.html を参考に生成しています．
#### Sample
```java
Capture cam;
String[] cameras; // 利用できるカメラのリスト
int select = 0; // 選択しているカメラ

void setup() {
  // 利用できるカメラリストを取得
  cameras = Capture.list();

  // 利用できるカメラがあれば，起動
  if (cameras.length != 0) {
    camSet();
  }
}

void draw() {
  // カメラが利用できなければ，終了
  if (cameras.length == 0) {
    println("There are no cameras available for capture.");
    noLoop();
    exit();
  }

  // 全画面になるよう拡大・縮小する
  float zoom = 0;
  if (cam.available()) {
    cam.read();
    zoom = max(width/cam.width, height/cam.height);
  }
  // カメラ画像を描画
  imageMode(CENTER);
  image(cam, width/2, height/2, cam.width*zoom, cam.height*zoom);
}

// カメラ切り替え
void camSet() {
  if (cameras.length == 0) return;
  if (cam != null) {
    cam.stop();
  }
  cam = new Capture(this, cameras[select%cameras.length]);
  cam.start();
}

void mousePressed() {
  if (cam == null || !cam.available()) return;
  select++;
  camSet();
}
```

### vibrate
https://developer.mozilla.org/ja/docs/Web/API/Navigator.vibrate を利用できます．
#### Sample
```java
void setup(){}
void draw(){}
void mousePressed() {
  vibrate(100); // vibrate for 100ms
}
```

### devicemotion / deviceorientation
端末の傾き，加速度，重力加速度が取得できます．

詳しくは，https://developer.mozilla.org/ja/docs/DOM/Orientation_and_motion_data_explained をご覧ください．

|Name|Description|
|:--:|:--|
|accelerationX|加速度X|
|accelerationY|加速度Y|
|accelerationZ|加速度Z|
|gravityX|重力加速度X|
|gravityY|重力加速度Y|
|gravityZ|重力加速度Z|
|deviceRotateX|beta回転|
|deviceRotateY|gamma回転|
|deviceRotateZ|alpha回転|

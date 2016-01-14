<?php
/*エラー処理*/
function returnError($msg) {
  header("HTTP", true, "400");
  header("Content-Type: application/json;");
  echo json_encode(array(
    "status" => "error",
    "message" => $msg
  ));
  exit(-1);
}

/*初期設定*/
mb_internal_encoding('UTF-8');
date_default_timezone_set("Asia/Tokyo");

/* POSTされたJSONを読み込む */
$json_string = file_get_contents('php://input');
$request = json_decode($json_string, true);

/*モード指定されていない場合、エラーを返す*/
if (is_null($request) || !isset($request["mode"]) || empty($request["mode"])) returnError("Invalid value.");
/* 利便性向上のため，小文字に統一 */
$request["mode"] = mb_strtolower($request["mode"]);

/* switch文で分岐 */
switch ($request["mode"]) {
  /*セーブ*/
  case "save":
    /* ハッシュ値確認 */
    if (!isset($request["id"]) || !isset($request["hash"])) {
      if (hash_hmac('sha256', "vanilla".$request["id"]."salt", false) !== $request["hash"])
        returnError("Invalid value.");
    }
    /* 必要な引数を確認 */
    $required_keys = explode(" ", "id title code username published secretkey");
    /* 必要な引数がない場合エラーを返す */
    foreach ($required_keys as $key) {
      if (!isset($request[$key]) || $request[$key] === "") returnError("Invalid value.");
    }
    /* SQLを実行 */
    $request['time'] = time();
    $keys = array_merge(array('time'), $required_keys);
    try {
      $pdo = new PDO("sqlite:CMP.sqlite");
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
      $st = $pdo->prepare("REPLACE INTO pdeStore ( ". join("," , $keys) . ") VALUES( :". join(", :" , $keys) . ")");
      foreach ($keys as $key) {
        $st->bindValue(":${key}", $request[$key], PDO::PARAM_STR);
      }
      if (!$st->execute()) returnError("DB Error.\n".$st->errorInfo()[2]);
    } catch (PDOException $err) {
      unset($pdo);
      returnError($err->getMessage());
    }
    header("Content-Type: application/json;");
    echo json_encode(array("status" => "success"));
    break;
  // /*デリート*/
  // case "delete":
  // $keys = explode(" ", "id");
  // /* 必要な引数がない場合エラーを返す */
  // foreach ($keys as $key) {
  //   if (!isset($request[$key]) || empty($request[$key])) returnError("Invalid value.");
  // }
  // /* SQLを実行 */
  // try {
  //   $pdo = new PDO("sqlite:CMP.sqlite");
  //   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  //   $st = $pdo->prepare("DELETE FROM pdeStore WHERE id = :id");
  //   $st->bindValue(':id', $request["id"]);
  //   $st->execute();
  // } catch (PDOException $err ) {
  //   unset($pdo);
  //   returnError($err->getMessage());
  // }
  // /*実行結果を返す*/
  // echo json_encode(array("status" => "success",
  //                        "mode" => "DELETE"));
  // break;
  /* 存在するか */
  case "exist":
    /* 必要な引数を確認 */
    $required_keys = explode(" ", "id");
    /* 必要な引数がない場合エラーを返す */
    foreach ($required_keys as $key) {
      if (!isset($request[$key]) || $request[$key] === "") returnError("Invalid value.");
    }
    $keys = $required_keys;
    /* SQLを実行 */
    try {
      $pdo = new PDO("sqlite:CMP.sqlite");
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
      $where = array();
      foreach ($keys as $key) {
        array_push($where, "${key} = :${key}");
      }
      $st = $pdo->prepare("SELECT * FROM pdeStore WHERE " . join(" AND " , $where));
      foreach ($keys as $key) {
        $st->bindValue(":${key}", $request[$key], PDO::PARAM_STR);
      }
      $st->execute();
      $data = $st->fetch(PDO::FETCH_ASSOC);
      if (!$data) returnError("Not match.");
    } catch (PDOException $err) {
      unset($pdo);
      returnError($err->getMessage());
    }
    /*実行結果を返す*/
    header("Content-Type: application/json;");
    echo json_encode(array("status" => "success"));
    break;
  /*ロード*/
  case "load":
    /* 必要な引数を確認 */
    $required_keys = explode(" ", "id secretkey");
    /* 必要な引数がない場合エラーを返す */
    foreach ($required_keys as $key) {
      if (!isset($request[$key]) || $request[$key] === "") returnError("Invalid value.");
    }
    $keys = $required_keys;
    /* SQLを実行 */
    try {
      $pdo = new PDO("sqlite:CMP.sqlite");
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
      $where = array();
      foreach ($keys as $key) {
        array_push($where, "${key} = :${key}");
      }
      $st = $pdo->prepare("SELECT * FROM pdeStore WHERE " . join(" AND " , $where));
      foreach ($keys as $key) {
        $st->bindValue(":${key}", $request[$key], PDO::PARAM_STR);
      }
      $st->execute();
      $data = $st->fetch(PDO::FETCH_ASSOC);
      if (!$data) returnError("Not match.");
    } catch (PDOException $err) {
      unset($pdo);
      returnError($err->getMessage());
    }
    /*実行結果を返す*/
    unset($data["secretkey"]);
    header("Content-Type: application/json;");
    $data["hash"] = hash_hmac('sha256', "vanilla".$data["id"]."salt", false);
    echo json_encode(array("status" => "success", "data" => $data));
    break;
  default:
    /* どれにも当てはまらなかったら，エラーを返す */
    returnError("Invalid value.");
    break;
}
exit();

<?php

$char = array_merge(range('0','9'), range('a', 'z'), range('A', 'Z'));
$time = microtime(true)*1000;

$unitime = (encode($time, $char)).rand(0,9);

header("Content-Type: application/json");
echo json_encode(array(
  "qr_id" => $unitime,
  "hash" => hash_hmac('sha256', "vanilla".$unitime."salt", false)
));
exit();

function encode($number, $char){
    $result = "";
    $base = count($char);

    while($number > 0){
        $result = $char[ fmod($number, $base) ] . $result;
        $number = floor($number / $base);
    }
    return ($result === "" ) ? 0 : $result;
}

?>

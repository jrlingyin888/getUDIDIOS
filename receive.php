<?php
$data = file_get_contents('php://input');
$plistBegin   = '<?xml version="1.0"';
$plistEnd   = '</plist>';
$pos1 = strpos($data, $plistBegin);
$pos2 = strpos($data, $plistEnd);
$data2 = substr ($data,$pos1,$pos2-$pos1);
$xml = xml_parser_create();
xml_parse_into_struct($xml, $data2, $vs);
xml_parser_free($xml);

$UDID = "";

$CHALLENGE = "";

$DEVICE_NAME = "";

$DEVICE_PRODUCT = "";

$DEVICE_VERSION = "";

$iterator = 0;

$arrayCleaned = array();
foreach($vs as $v){
    if($v['level'] == 3 && $v['type'] == 'complete'){

    $arrayCleaned[]= $v;

    }
$iterator++;

}

$data = "";
$iterator = 0;

foreach($arrayCleaned as $elem){

    $data .= "\n==".$elem['tag']." -> ".$elem['value']."<br/>";

    switch ($elem['value']) {

        case "CHALLENGE":

            $CHALLENGE = $arrayCleaned[$iterator+1]['value'];

            break;

        case "DEVICE_NAME":

            $DEVICE_NAME = $arrayCleaned[$iterator+1]['value'];

            break;

        case "PRODUCT":

            $DEVICE_PRODUCT = $arrayCleaned[$iterator+1]['value'];

            break;

        case "UDID":

            $UDID = $arrayCleaned[$iterator+1]['value'];

            break;

        case "VERSION":

            $DEVICE_VERSION = $arrayCleaned[$iterator+1]['value'];

            break;                       

        }
        $iterator++;

}

function encryptAES($content, $key) {
    // AES 加密使用的初始向量（必须与 Swift 代码中的一致）
    $initVector = '16-Bytes--String';  // 你需要确保这与 Swift 中的 kInitVector 相同
    $keySize = 16; // 密钥长度（通常为 16 字节，对于 AES-128）

    // 使用 openssl_encrypt 进行 AES 加密
    $encryptedData = openssl_encrypt(
        $content,                   // 待加密的内容
        'AES-128-CBC',               // 使用 AES-128-CBC 模式
        $key,                        // 加密密钥
        OPENSSL_RAW_DATA,            // 返回原始二进制数据
        $initVector                  // 初始化向量
    );

    // 如果加密成功，进行 Base64 编码并返回结果
    if ($encryptedData !== false) {
        return base64_encode($encryptedData);
    }

    return ''; // 加密失败，返回空字符串
}

$key = "5231zxcvbnjef7941";          // 加密的密钥

$params = "UDID=".$UDID."&CHALLENGE=".$CHALLENGE."&DEVICE_NAME=".$DEVICE_NAME."&DEVICE_PR ODUCT=".$DEVICE_PRODUCT."&DEVICE_VERSION=".$DEVICE_VERSION;

$encryptedUDID = encryptAES($UDID, $key);

// header("Location: http://dev.skyfox.org/udid?data=".rawurlencode($params));
header('HTTP/1.1 301 Moved Permanently');
header("Location: com.jerry.sign://?deviceUDID=".rawurlencode($encryptedUDID)."&CHALLENGE=".rawurlencode($CHALLENGE)."&DEVICE_NAME=".rawurlencode($DEVICE_NAME)."&DEVICE_PRODUCT=".rawurlencode($DEVICE_PRODUCT)."&DEVICE_VERSION=".rawurlencode($DEVICE_VERSION));
?>
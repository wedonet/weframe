<?php

require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';




//��ʼ����־
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);


//�١���ȡ�û�openid
$tools = new JsApiPay();


$openId = $tools->GetOpenid();
die($openId);

<?php

require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';




//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);


//①、获取用户openid
$tools = new JsApiPay();


$openId = $tools->GetOpenid();
die($openId);

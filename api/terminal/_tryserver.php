<?php

/* 检测服务端是否掉线，掉线短信通知 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_socket.php';
require_once (syspath . '_inc/cls_sms.php');

class myapi extends cls_api {

    function __construct() {
        parent::__construct();


        switch ($this->act) {
            case '':
                $this->pagemain();
                break;
        }
    }

    function pagemain() {



        $a_sms = Array(
            '13512817425', //wenhui
            '13512031275', //lijingying
            '13043272481', //sunyilin
            '15022727634', //mayi
            '18222356065' //fuxudong
        );
        //if (!isset($GLOBALS['config']['serverip'])) {
        $ip = '101.200.123.1';
        //} else {
        //    $ip = $GLOBALS['config']['serverip'];
        //}

        $port = '50601';


        $c_socket = new cls_socket($ip, $port);


        $con = $c_socket->connect();


        //测试web服务器
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.ejshendeng.com/bying/index.php");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出  
        $result = curl_exec($ch);
        curl_close($ch);



        if (TRUE !== $con) {
            $c_sms = new cls_sms ();

            $content = $c_sms->getsendmsg(36);

            foreach ($a_sms as $v) {
                $para['uid'] = 0;
                $para['comid'] = 0;
                $result = $c_sms->send($v, $content, $para);
            }

            echo 'ee,socket服务器掉线了';

            return;
        } elseif (false === strpos($result, '请重新扫码进入')) {
				$c_sms = new cls_sms ();

            $content = $c_sms->getsendmsg(36);
            
            foreach ($a_sms as $v) {
                $para['uid'] = 0;
                $para['comid'] = 0;
                $result = $c_sms->send($v, $content, $para);
            }
            echo 'ee,web服务器掉线了';
        } else {

            echo '<html>';
            echo '<title>监控柜机服务器</title>';

            echo '<head><meta http-equiv="refresh" content="300"></head>';


            echo 'haha';
        }
    }

}

$myapi = new myapi();
unset($myapi);

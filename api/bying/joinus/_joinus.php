<?php

require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'bying/_main.php'; //公共文件

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
        //设置了此缓存
//        if (isset($_SESSION[CacheName . 'count'])) {
//           
//        } else {
//            $_SESSION[CacheName . 'count']='1';
//            $this->insert();
//        }  
       // $door=$this->main->rqid('door');
         //$this->j['doorid'] = $door;
        if (isset($_COOKIE [CacheName . 'count'])) {
            
        } else {
            setcookie(CacheName . 'count', time());
            $this->insert();
        }
    }

    function insert() {
        $rs['myip'] = $this->getip();
        $rs['type'] = 0;
        $rs['stimeint'] = time();
        $rs['stime'] =  date('Y-m-d H:i:s',time());;
        $rs['num'] = 0;
        $this->pdo->insert(sh . '_count', $rs);
    }

//    function update() {
//        $rs['myip'] = $this->getip();
//        $rs['num'] = $rs['num']+1;
//     
//        $this->pdo->update(sh . '_count', $rs['num'],$rs['myip']);
//    }

    function getip() {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = '';
        }
//        if (!empty($_SERVER["SERVER_PORT"])) {
//            $port = $_SERVER["SERVER_PORT"];
//        } else {
//            $port = '0000';
//        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);
        return $cip ;
    }

}

$myapi = new myapi();
unset($myapi);




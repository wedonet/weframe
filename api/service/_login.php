<?php

/* 用户登录 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
error_reporting(E_ERROR | E_WARNING | E_PARSE);

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
    
        $this->pagemain();
        switch ($this->act) {
            case '':

                //$this->pagemain();
                // $_POST['outtype'] = 'json';
                // $this->output();
                break;
            case 'loginin':
                $this->loginin();
                $this->output();
                break;
            case 'loginout':
                $this->loginout();
                $this->output();
                break;
            default:
                break;
        }
    }

    function pagemain() {
        //syspath . 'service/reg.php'
        $_SESSION[CacheName . 'HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
        // print_r( $_SESSION[CacheName.'HTTP_REFERER']);
        //print_r($_SERVER['HTTP_REFERER']);
        //print_r( $_SESSION[CacheName.'procomeurl']);die;
       if (!strpos($_SESSION[CacheName . 'HTTP_REFERER'], 'service/login.php') && !strpos($_SESSION[CacheName . 'HTTP_REFERER'], 'service/reg.php')&& !strpos($_SESSION[CacheName . 'HTTP_REFERER'], 'service/searchpass.php')){
            $_SESSION[CacheName . 'procomeurl'] = $_SERVER['HTTP_REFERER'];
            // print_r($_SESSION[CacheName.'procomeurl']);die;
        }


        //print_r($_SESSION[CacheName.'procomeurl']);die;
    }

    function loginin() {
        $c_user = new cls_user();

        $this->main->posttype = 'post';

        $u_mobile = $this->main->request('u_mobile', '手机号', 11, 11, 'mobile');
        $u_pass = $this->main->request('u_pass', '密码', 6, 20, 'char');

        if (!$this->ckerr())
            return false;

        if ($c_user->checklogin('', $u_pass, 'user', '', $u_mobile)) {
            $this->j['success'] = 'y';

            //从缓存中提取订单号，更新数据库中订单uid，将游客为0的uid更新真正uid（主要是未登陆的订单）
            if (isset($_SESSION[CacheName . 'orders'])) {
                $orders = $_SESSION[CacheName . 'orders'];
                $this->main->GetUserInfo();
                $rs['uid'] = $this->main->user['id'];
                // print_r($rs);
                // print_r($orders);die;
                $rs['ugic'] = $this->main->user['u_gic'];
                //print_r($rs);
                // print_r($orders);die;
                if ($rs['ugic'] == 'user') {
                    $sql = 'update ' . sh . '_order';
                    $sql.=' set uid=' . $rs['uid'];
                    $sql.=' where id in(' . $orders . ')';
                    $this->pdo->doSql($sql);
                }
            }
        } else {

            $this->ckerr();
            return false;
        }
    }

    function loginout() {
        $c_user = new cls_user();

        $c_user->loginout();

        $this->j['success'] = 'y';
    }

}

$myapi = new myapi();
unset($myapi);

<?php

/* 补货人员登录接口 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';




/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        switch ($this->main->ract()) {
            case '':
                break;
            case 'loginin':
                $_POST['outtype'] = 'json';
                $this->loginin();
                $this->output();
                break;
            case 'loginout':
                //$_POST['outtype'] = 'json';
                $this->loginout();
                $this->output();
                break;
        }
    }

    /* 登录，跟据用户名，密码，角色，判断是否登录成功，并返回用户信息 */

    function loginin() {
        $we = & $GLOBALS['main'];
        $c_user = new cls_user();

        $we->posttype = 'post';

        $u_name = $we->request('u_name', '用户名', 6, 20, 'char', 'invalid');
        $u_pass = $we->request('u_pass', '密码', 6, 20, 'char');

        $this->ckerr();

        $result = $c_user->checklogin($u_name, $u_pass, 'bizer', 'replenishment');

        if ($result) {
            $this->j['success'] = 'y';
        } else {
            $this->ckerr();
        }
    }
    
    function loginout(){
        $c_user = new cls_user();
        
        $c_user->loginout();
        
        $this->j['success'] = 'y';
    }

}

$myapi = new myapi();
unset($myapi);
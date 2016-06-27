<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';

/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

  

        switch ($this->act) {
            case '': 
                break;
            case 'loginin':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->loginin();
                $this->output();
                break;
            case 'loginout':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->loginout();
                $this->output();
                break;            
            default:
                break;
        }
    }



    function loginin() {
        $c_user = new cls_user();
        $this->main->posttype='post';
        
        $u_name = $this->main->request( 'u_name', '用户名', 4, 20, 'char', 'invalid');
        $u_pass = $this->main->request( 'u_pass', '密码',  4, 20, 'char');
        
        $this->ckerr();
        
        
        if( $c_user->checklogin($u_name, $u_pass, 'admin')) {
            $this->j['success'] = 'y';
        } else{
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
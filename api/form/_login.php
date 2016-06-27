<?php
/* 用户登录 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';



class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        
        switch ($this->act) {
            case '': 
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



    function loginin() {
        $c_user = new cls_user();

        $this->main->posttype='post';
        
        $u_mobile = $this->main->request( 'u_mobile', '手机号', 11, 11, 'mobile');
        $u_pass = $this->main->request( 'u_pass', '密码',  6, 20, 'char');

        if(!$this->ckerr()) return false;
        

        
        
        if( $c_user->checklogin('', $u_pass, 'user', '', $u_mobile)) {
            $this->j['success'] = 'y';
        } else{
            $this->ckerr();
            return false;
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
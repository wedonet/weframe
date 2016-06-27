<?php
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';


require_once ApiPath . 'bying/_main.php'; //调查通用数据


class myapi extends cls_api {

    function __construct() {
        parent::__construct();

      //  $this->modulemain = new cls_modulemain();

       // print_r(pr());die;
        switch ($this->act) {
            case '':
               // $this->pagemain();
                $this->output();
                break;
            case 'nsave':
                $this->nsave();
                $this->output();
                break;
            default :
                break;
        }
    }
    function nsave() {
       $this->main->posttype='post';
        $f_name = $this->main->request('f_name', '姓名', 1, 20, 'char','ench',true);
        $f_mobile =  $this->main->request('f_mobile', '手机号', 11, 11, 'mobile');
        $f_field =  $this->main->request('f_field', '领域', 1, 20, 'char','encode',false);
        $f_message =  $this->main->request('f_message', '留言', 1, 150, 'char','encode',false);
//        if ('' == $f_name) {           
//            $GLOBALS['errmsg'][] = '请输入姓名';       
//            }
//        if ('' == $f_mobile) {      
//            $GLOBALS['errmsg'][] = '请输入正确的手机号码';
//        }
    
        if (!$this->ckerr()) {                 
            return false;
        }   
       
        $pdo = & $GLOBALS['pdo'];
//        try {
//            $pdo->begintrans();

            $currenttime = time();

            /* 答案入库 */          
            $rs['stime'] = date('Y-m-d H:i:s', $currenttime);
            $rs['stimeint'] = $currenttime;
            $rs['f_name'] = $f_name;
            $rs['f_mobile'] = $f_mobile;
            $rs['f_field'] = $f_field;
            $rs['f_message'] = $f_message;
            $answerid = $this->pdo->insert(sh . '_joinusform', $rs);         
            
//            $pdo->submittrans();
//        } catch (PDOException $e) {
//            $pdo->rollbacktrans();
//            echo ($e);
//            die();
//        }    
        $this->j['success'] = 'y';
    }  
}
$myapi = new myapi();
unset($myapi);

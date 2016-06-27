<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once '_main.php'; /* 业务管理通用数据 */

/* */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
       

        $this->act = $this->main->ract();

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;
            case'select';
                $this->selectorder();
                $this->output();
                break;
            case'creat';
               $this->myform();
               $this->output();
               break;
           case 'edit':
                $this->getorder();
                $this->output();
                break;
            case 'nsave': //保存新用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savenew();
                $this->output();
                break;
            case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;
           
            
        }
    }

       
    function mylist() {
        for ($i = 0; $i < 10; $i++) {
            $a[$i]['id'] = $i;//流水号
            $a[$i]['uid'] = '1111';//操作人id
            $a[$i]['unick'] = '小张';//操作人姓名
            $a[$i]['content'] = '商品过期';//换货原因
            $a[$i]['stimeint'] = '2015-11-02';//操作时间
            $a[$i]['doorid'] = '555';//柜门id
            
        }
        $this->j['data'] = $a;
        $this->j['userlist']['rs'] = $a;
        $this->j['userlist']['total'] = 100;
    }

    /* 提取平台的全设备 */
  
    
    function formedit(){
        
        
    }
     function myform(){
        
    }
     function getorder() {
       
    }
    
    function esave() {
        if (1 == 1) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
            $this->j['errinput'] = 'u_mobile';
        }
    }
    
    function savenew() {
        if (1 == 1) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
            $this->j['errinput'] = 'id,ic';
        }
    }

}

$myapi = new myapi();//建立类的实例
unset($myapi);//释放类占用的资源
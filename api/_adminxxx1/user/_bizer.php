<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';

/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        
        $this->act = $this->main->ract();

        switch ($this->act) {
            case '':
                $this->mylist();
                $this->output();
                break;
            case 'creat':
                //$this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getuser();
                $this->output();
                break;
            case 'admin': //管理用户
                $this->getuser();
                $this->output();
                break;
            
            case 'ischeck':
            case 'uncheck':
            case 'islock':
            case 'unlock':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->doadmin();
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
            case 'savepass':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savepass();
                $this->output();
                break;
            case 'del': //删除用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->del();
                $this->output();
        }
    }

    /* 用户列表 */

    function mylist() {
        for ($i = 0; $i < 10; $i++) {
            $a[$i]['id'] = $i;
            $a[$i]['u_name'] = '商家' . $i;  
            $a[$i]['u_gname'] = '商家';
            $a[$i]['ischeck'] = 1;
            $a[$i]['islock'] = 0;
        }

        $this->j['userlist']['rs'] = $a;
        $this->j['userlist']['total'] = 100;
    }

    /* 保存用户 */

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

    function savepass() {
        if (1 == 1) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
            $this->j['errinput'] = 'u_pass';
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
            $this->j['errinput'] = 'ic,title';
        }
    }

    /* 提取用户 */

    function getuser() {

        $a['id'] = 0;
        $a['u_name'] = '商家1';   
        $a['u_gname'] = '商家';
        $a['u_phone'] = '0---26000000';
        $a['u_mobile'] = '13000000000';
        $a['u_mail'] = 'a@b.com';
        $a['ischeck'] = 1;
        $a['islock'] = 0;

        $this->j['thisuser'] = $a;
    }

    function del() {
        if (1 == 1) {
            $this->j['success'] = 'y';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
        }
    }

    /*对用户的各种操作*/
    function doadmin(){
         if (1 == 1) {
            $this->j['success'] = 'y';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
        }       
    }
}

$myapi = new myapi();
unset($sys_admin_user);
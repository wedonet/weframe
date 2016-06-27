<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
require_once syspath . '_inc/cls_biz.php';
require_once ApiPath . '_adminxxx1/_main.php';
class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        
		 $this->modulemain = new cls_modulemain();
	
			/* 检测权限 */
			if (!$this->modulemain->haspower()) {
				$this->output();
				return false;
			}
			
        LoadClass('c_biz', 'cls_biz');
        $this->c_biz = $GLOBALS['c_biz'];


        /* 什么情况下必须返回json格式 */
        $jsonact = array('json'
            , 'dofinduser'
            , 'save'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }



        switch ($this->act) {
            case '':
                $this->thismain();
                $this->output();
                break;
            case 'myform':
                $this->myform();
                $this->output();
                break;
            case 'save':
                $this->savenew();
                $this->output();
                break;


            /* 下面是返回json格式的 */
            case 'dofinduser';
                $this->dofinduser();
                $this->output();
                break;
        }
    }

    function thismain() {
        
    }

    /* 返回Uid */

    function dofinduser() {
        $this->main->posttype = 'post';

        $we = & $this->main;

        $u_name = $we->request('u_name', '用户名', 2, 20, 'char', 'invalid', false);
        $u_mobile = $we->request('u_mobile', '手机', 11, 11, 'mobile', '', false);

        $this->ckerr();

        if ('' == $u_name And '' == $u_mobile) {
            $this->ckerr('用户名或手机至少要填一个');
        }

        //检测用户
        $sql = 'select * from `' . sh . '_user` where 1 ';

        $sql .= ' and islock=0';
        $sql .= ' and isdel=0';

        if ('' != $u_name) {
            $sql .= ' and u_name="' . $u_name . '" ';
        }

        if ('' != $u_mobile) {
            $sql .= ' and u_mobile="' . $u_mobile . '"';
        }

        $result = $this->pdo->fetchOne($sql);

        if (false == $result) {
            $this->ckerr('没有这个用户或用户被删除或锁定');
        }

        $this->j['uid'] = $result['id'];
    }

    /* 跟据用户id返回用户信息 */

    function myform() {
        $uid = $this->main->rqid('uid');

        /* ### 入款用户信息 */
        $a_user = $this->c_biz->getuserbyid($uid);

        $this->j['myuser'] = $a_user;

        $this->j['comanme'] = '';
        /* ### 如果是商家再返回一个商家名称 */
        if ('bizer' == $a_user['u_gic']) {
            $sql = 'select title from `' . sh . '_com` where uid=:uid';

            $para[':uid'] = $uid;
            $result = $this->pdo->fetchOne($sql, $para);


            $this->j['myuser']['comname'] = $result['title'];
        }


        /* ### 入款方式 */
        require_once( syspath . '_inc/cls_money.php' );
        $c_money = new cls_money();

        $myway = $c_money->myway;
 
        $this->j['myway'] = $myway;
    }

    function savenew() {
        $we = & $this->main;
        $pdo = & $this->pdo;
        $c_money = new cls_money;
        

        $we->posttype = 'post';

        $uid = $we->request('uid', '用户id', 1, 999999999, 'int');
        $myvalue = $we->request('myvalue', '金额', '0.01', '100000', 'num');
        //$mytype = $we->request('mytype', '入款种类', 1000, 90000, 'int');
        $myway = $we->request('myway', '出款方式', 2, 10, 'char', 'invalid');
        $formdate = $we->request('formdate', '凭证日期', 2, 20, 'date');
        $formcode = $we->request('formcode', '凭证号', 2, 20, 'char', 'encode');
        $other = $we->request('other', '备注', 1, 500, 'char', 'encode');

        $this->ckerr();

        /* 数据模式处理 */
        $formdate = strtotime($formdate);


        /* ====检测数据正确性 */
        /*在类里进行检测了*/
        

        /* 提取用户信息 */
        $a_user = $this->c_biz->getuserbyid($uid);
        if (false == $a_user) {
            $this->ckerr(1022);
        }

        $a['title'] = '出款';
        $a['mywayic'] = $myway;
        $a['amoun'] = $myvalue * 100;
        $a['formcode'] = $formcode;
        $a['formdate'] = $formdate;
        $a['uid'] = $uid;
        $a['orderid'] = 0;
        $a['duid'] = $this->main->user['id'];
        $a['dnick'] = $this->main->user['u_nick'];
        $a['other'] = $other;
        



        try {
            $pdo->begintrans();

            switch ($a_user['u_gic']) {
                case 'user':
                    $a['acceptgroup'] = 'user';
                    $a['comid'] = 0;
                    $a['mytype'] = 2020;
                    $a['action'] = 'substract';
                    
                    $result = $c_money->domoney($a);               
                    if(false === $result) break;
                    
                    //再给平台出一笔
                    
                    $a['acceptgroup'] = 'plat';
                    $a['comid'] = 0;     
                    $a['mytype'] = 6010;
                    $a['action'] = 'substract';
                    $result = $c_money->domoney($a);
        
                    
                    break;
                case 'bizer': //给店铺出款
                    $a['acceptgroup'] = 'com';
                    $a['comid'] = $a_user['comid'];                    
                    $a['mytype'] = 4020;
                    $a['action'] = 'substract';
                    
                    $result = $c_money->domoney($a);
                    if(false === $result) break;
                  
                    //再给平台出一笔
                    $a['acceptgroup'] = 'plat';
                    $a['comid'] = $a_user['comid'];      
                    $a['mytype'] = 6010;
                    $a['action'] = 'substract';
                    
                    $result = $c_money->domoney($a);       
  
                    
                    break;                
                case 'admin':
                    /**/
                    $a['acceptgroup'] = 'plat';
                    $a['comid'] = 0;
                    $a['mytype'] = 6010;
                    $a['action'] = 'substract';
                    
                    $result = $c_money->domoney($a);    
                    break;

                default:
                    $this->ckerr(1022);
                    break;
            }

            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->ckerr();
      

        $this->j['success'] = 'y';
        $this->j['msg'] = '保存成功';
    }

}

$myapi = new myapi();
unset($myapi);
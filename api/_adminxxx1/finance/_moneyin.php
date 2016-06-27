<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
require_once syspath . '_inc/cls_biz.php';
require_once ApiPath . '_adminxxx1/_main.php';
require_once AdminApiPath . '_main.php';

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

        $u_gic = $this->main->ract('u_gic');

        $we = & $this->main;

        switch ($u_gic) {
            case 'admin':
            case 'bizer':
                $u_name = $we->request('u_name', '用户名', 2, 20, 'char', 'invalid');
                break;
            case 'user':
                $u_mobile = $we->request('u_mobile', '手机', 11, 11, 'mobile');
                break;
        }

        if (!$this->ckerr()) {
            return false;
        }


        //检测用户
        $sql = 'select * from `' . sh . '_user` where 1 ';

        $sql .= ' and islock=0';
        $sql .= ' and isdel=0';


        switch ($u_gic) {
            case 'admin':
                $sql .= ' and u_name="' . $u_name . '" ';
                $sql .= ' and u_gic="admin" ';
                break;
            case 'bizer':
                $sql .= ' and u_name="' . $u_name . '" ';
                $sql .= ' and u_roleic="sys" ';
                $sql .= ' and u_gic="bizer" ';
                break;
            case 'user':
                $sql .= ' and u_mobile="' . $u_mobile . '"';
                $sql .= ' and u_gic="user" ';
                break;
        }

        $result = $this->pdo->fetchOne($sql);

        if (false == $result) {
            $this->ckerr('没有这个用户或用户被删除/锁定');
        }

        $this->j['uid'] = $result['id'];
        $this->j['u_gic'] = $result['u_gic'];
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
            $sql = 'select * from `' . sh . '_com` where uid=:uid';

            $para[':uid'] = $a_user['id'];
            $result = $this->pdo->fetchAll($sql, $para);


            $this->j['myuser']['comlist'] = $result;
        }


        /* ### 入款方式 */
        require_once( syspath . '_inc/cls_money.php' );
        $c_money = new cls_money();

        $myway = $c_money->moneyway;

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
        $myway = $we->request('myway', '入款方式', 2, 10, 'char', 'invalid');
        $formdate = $we->request('formdate', '凭证日期', 2, 20, 'date');
        $formcode = $we->request('formcode', '凭证号', 2, 20, 'char', 'encode');
        $other = $we->request('other', '备注', 1, 500, 'char', 'encode');

        $comid = $we->request('comid', '店铺', 1, 999999999, 'int', '', false);



        if (!$this->ckerr()) {
            return false;
        }

        /* 数据模式处理 */
        $formdate = strtotime($formdate);


        /* ====检测数据正确性 */
        /* 检测凭证号有没有重复的，只检测管理员的就行了，其实地方入，管理员就入了 */
        if ($c_money->hasformcode(sh . '_moneyplat', $formcode, 5010)) {
            $this->ckerr('已经入过这笔款了，请不要重复操作！');
        }



        /* 提取用户信息 */
        $a_user = $this->c_biz->getuserbyid($uid);
        if (false == $a_user) {
            $this->ckerr(1022);
            return false;
        }

        /* 如果给店铺入款，检测是不是选择了店铺 */
        if ('bizer' == $a_user['u_gic'] AND '' == $comid) {
            $this->ckerr('请选择入款店铺');
            return false;
        }


        $a['title'] = '充值';
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
                    $a['mytype'] = 1010;
                    $a['action'] = 'add';
                   $a['myfrom'] = 'shendeng';
                    $result = $c_money->domoney($a);



                    //再给平台入一笔

                    $a['acceptgroup'] = 'plat';
                    $a['comid'] = 0;
                    $a['mytype'] = 5010;
                    $a['action'] = 'add';
                    $result = $c_money->domoney($a);

                    break;
                case 'bizer': //给店铺入款
                    $a['acceptgroup'] = 'com';
                    $a['comid'] = $comid;
                    $a['mytype'] = 3020;
                    $a['action'] = 'add';

                    $result = $c_money->domoney($a);

                    //再给平台入一笔
                    $a['acceptgroup'] = 'plat';
                    $a['comid'] = $comid;
                    $a['mytype'] = 5010;
                    $a['action'] = 'add';

                    $result = $c_money->domoney($a);

                    break;
                case 'admin':
                    /**/
                    $a['acceptgroup'] = 'plat';
                    $a['comid'] = 0;
                    $a['mytype'] = 5010;
                    $a['action'] = 'add';

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



        $this->j['success'] = 'y';
        $this->j['msg'] = '保存成功';
    }

}

$myapi = new myapi();
unset($myapi);

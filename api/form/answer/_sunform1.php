<?php

/* 补货首页 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
require_once syspath . '_inc/cls_form.php';

require_once ApiPath . 'form/_main.php'; //公共文件

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }


        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
            case 'nsave':
                $this->nsave();
                $this->output();
                break;
        }
    }

    /**/

    function pagemain() {
        /* 提取调查 */
//        $sql = 'select * from `'.sh.'_u` where 1 ';
//      //  $sql .= ' and mystatus="doing" ';  所有状态的问卷均显示
//        $sql .= ' order by mycount desc ';
//        
//        $this->j['list'] = $this->pdo->fetchAll($sql);
    }

    function nsave() {

        $c_money = new cls_money();
        $c_form = new cls_form();

        $formid = $this->main->rqid('formid');

        $main = & $this->main;
        $main->posttype = 'post';
        
        
        $f_fullname = $main->request('f_fullname', '姓名', 2, 10, 'char');
       


        if (!$this->ckerr()) {
            return false;
        }

        /* 提取这个调查 */
        $a_form = $c_form->getformbyid($formid);
        if(false == $a_form){
            $this->ckerr('没找到这个调查');
            return false;
        }


        /* 检测是不是答过这个调查了 */
        if( $c_form->hasdoneform($formid)){
            $this->ckerr('已经答过这个调查了');
            return false;
        }       


        /* ==============================
         * 事务处理
         */
        $pdo = & $GLOBALS['pdo'];
        try {
            $pdo->begintrans();

            $currenttime = time();


            /* 答案入库 */
            $rs['formid'] = $formid;
            $rs['uid'] = $this->main->user['id'];
            $rs['stime'] = date('Y-m-d H:i:s', $currenttime);
            $rs['stimeint'] = $currenttime;

            $rs['f_fullname'] = $f_fullname;



            $answerid = $this->pdo->insert(sh . '_form_sunform1', $rs);


            /* 添加进我答过题的表 */
            unset($rs);
            $rs['uid'] = $this->main->user['id'];
            $rs['formid'] = $formid;
            $rs['answerid'] = $answerid;
            $rs['stimeint'] = $currenttime;
            $rs['unick'] = $this->main->user['u_nick'];

            $doid = $this->pdo->insert(sh . '_formdolist', $rs);


            /* 入款虚拟币 */
            $money['uid'] = $this->main->user['id'];
            $money['amoun'] = $a_form['myvalue'];
            $money['formcode'] = $doid; //交易单号 ,对答卷 是 form_dolistid
            $money['formdate'] = $currenttime;

            $money['mytype'] = 'form';
            //$money['myway'] = 'form';
            $money['other'] = '';

            $c_money->vmoneytouser($money);

            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['myvalue'] = $a_form['myvalue'] / 100;
        $this->j['success'] = 'y';
    }

}

$myapi = new myapi();
unset($myapi);

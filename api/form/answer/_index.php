<?php

/* 人寿调查 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';

require_once ApiPath . 'form/_main.php'; //调查通用数据

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
            case 'save':
                $this->saveinfor();
                $this->output();
                break;
        }
    }

    /**/

    function pagemain() {
    }

    function saveinfor() {
        $c_money = new cls_money();

        $formid = $this->main->rqid('formid');

        $main = & $this->main;
        $main->posttype = 'post';
        $f_fullname = $main->request('f_fullname', '姓名', 2, 10, 'char');
        $f_mobile = $main->request('f_mobile', '手机号', 11, 11, 'mobile');

        if (!$this->ckerr()) {
            return false;
        }

        /* 提取这个调查 */
        $sql = 'select * from `' . sh . '_form` where 1 ';
        $sql .= ' and mystatus="doing" '; //只能填进行中的调查
        $sql .= ' and id =:formid';
        $sql .= ' and stimeint<' . time();
        $a_form = $this->pdo->fetchOne($sql, Array(':formid' => $formid));
        if (false == $a_form) {
            $this->ckerr('没找到这个调查');
            return false;
        }
        
        /*检测姓名是不是中文*/


        /*检测手机号是不是天津的*/
        $tianjinmobile = file_get_contents(dirname(__FILE__).'\tianjinmobile.txt'); //读取文件中的内容
        if( false === strpos($tianjinmobile, substr($f_mobile,0,7))){
            $this->ckerr('这个手机号不是天津的');
            return false; 
        }
        
        /*检测是不是答过这个调查了*/
        $sql = 'select count(*) from `'.sh.'_form_renshou1` where 1 ';
        $sql .= ' and uid=:uid ';
        $count = $this->pdo->counts($sql, Array(':uid'=>$this->main->user['id']));
        if($count>0){
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
            $rs['f_mobile'] = $f_mobile;

            $answerid = $this->pdo->insert(sh . '_form_renshou1', $rs);


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
        
        $this->j['myvalue'] = $a_form['myvalue']/100;
        $this->j['success'] = 'y';
    }
    


}

$myapi = new myapi();
unset($myapi);

<?php

/* 补货首页 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once (syspath . '_inc/cls_sms.php'); //验证码的类
require_once syspath . '_inc/cls_money.php'; //财务类
require_once syspath . '_inc/cls_form.php'; //表单类

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
            case 'vertify':
                $this->vertify();
                $this->output();
                break;
            case 'checkvertify':
                $this->checkarrcode();//验证手机验证码输入正确与否
                $this->output();
                break;
            case 'sendsms':
                $this->sendsms();
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

    /**/

    function pagemain() {


        /* 提取这个调查 */
        $formid = $this->main->rqid ('formid');
        $sql = 'select * from `' . sh . '_form` where 1 ';
        $sql .= ' and mystatus="doing" '; //只能填进行中的调查
        $sql .= ' and id =:formid';
        $sql .= ' and stimeint<' . time();

        $a_form = $this->pdo->fetchOne($sql, Array(':formid' => $formid));
        if (false == $a_form) {
            $this->ckerr('您提交慢了，最后一份问卷已经被答完！');
            return false;
        }
        $this->j['form'] = $a_form;
    }
    
    
    //检测手机验证码正确与否函数
    function checkarrcode(){


        $this->main->posttype = 'get';
        $main = & $this->main;  
        $smscode = $main->request('smscode', '激活码', 1, 999999, 'num'); //接收手机短信激活码-----------
        $f_mobile = $main->request('f_mobile', '手机号', 11, 11, 'mobile');
        
       // print_r($f_mobile);die;
        
        if (!$this->ckerr()) {
            return false;
        }
          /* ------检测验证码对不对---------- */
        $arrcode = $GLOBALS['cache']->get($f_mobile);
        if (false == $arrcode) {
            $this->ckerr('请重新获取短信验证码');
            return;
        }
        if (time() - $arrcode['time'] > 1800) {
            $this->ckerr('验证码已失效,请重新获取');
            return;
        }

        if ($smscode != $arrcode['code']) {
            $this->ckerr('短信验证码错误');
            return;
        }
        //----------验证激活码wen---------------------- 
        
        $this->j['success'] = 'y';
    }
    
    
    
    function nsave() {

        $c_money = new cls_money();

        $formid = $this->main->rqid('formid');

        $main = & $this->main;
        $main->posttype = 'post';


        $f_fullname = $main->request('f_fullname', '姓名', 2, 6, 'char');
        $f_mobile = $main->request('f_mobile', '手机号', 11, 11, 'mobile');
        $f_age = $main->request('f_age', '年龄', 1, 20, 'char', 'encode');
        $f_marriage = $main->request('f_marriage', '是否结婚', 1, 10, 'char', 'encode');
        $f_car = $main->request('f_car', '是否购车', 1, 10, 'char', 'encode');

        $smscode = $this->main->request('smscode', '激活码', 1, 999999, 'num'); //手机短信激活码-----------

        unset($GLOBALS['errmsg']); //释放原来的错误提示

        $GLOBALS['errmsg'] = array();


        if ('' == $f_fullname) {
            $GLOBALS['errmsg'][] = '请输入姓名';
        } else {
            if (!$this->isChineseName($f_fullname)) {//检测姓名是不是中文
                $GLOBALS['errmsg'][] = '请输入中文姓名';
            }
        }

        if ('' == $f_mobile) {
            $GLOBALS['errmsg'][] = '请输入手机号码';
        }
        if ('' == $f_age) {
            $GLOBALS['errmsg'][] = '请选择年龄';
        }
        if ('' == $f_marriage) {
            $GLOBALS['errmsg'][] = '请选择是否婚育';
        }
        if ('' == $f_car) {
            $GLOBALS['errmsg'][] = '请选择是否购车';
        }

       if (!$this->ckerr()) {
            return false;
        }
        
        /* ------检测验证码对不对---------- */
        $arrcode = $GLOBALS['cache']->get($f_mobile);
        if (false == $arrcode) {
            $this->ckerr('请重新获取短信验证码');
            return;
        }
        if (time() - $arrcode['time'] > 1800) {
            $this->ckerr('验证码已失效,请重新获取');
            return;
        }

        if ($smscode != $arrcode['code']) {
            $this->ckerr('短信验证码错误');
            return;
        }
        //----------验证激活码wen---------------------- 


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
            $this->ckerr('您提交慢了，最后一份问卷已经被答完！');
            return false;
        }



        /* 检测手机号是不是天津的 */
        $tianjinmobile = file_get_contents(dirname(__FILE__) . '\tianjinmobile.txt'); //读取文件中的内容
        if (false === strpos($tianjinmobile, substr($f_mobile, 0, 7))) {
            $this->ckerr('这个手机号不是天津的');
            return false;
        }

        /* 检测是不是答过这个调查了 */
        $sql = 'select count(*) from `' . sh . '_form_renshou1` where 1 ';
        $sql .= ' and uid=:uid ';
        $sql .= ' and formid=:formid ';
        $count = $this->pdo->counts($sql, Array(':uid' => $this->main->user['id'], ':formid' => $formid));

        if ($count > 0) {
            $this->ckerr('问卷您已答过，不能重复作答！');
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

            $rs['f_age'] = $f_age;
            $rs['f_marriage'] = $f_marriage;
            $rs['f_car'] = $f_car;



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


            /* 更新统计 */
            //$c_form = new cls_form();

            $sql = 'update `' . sh . '_form` set ';
            $sql .= ' donecount=donecount+1 ';
            $sql .= ' where id=' . $formid;
            $this->pdo->doSql($sql);

            $sql = 'update `' . sh . '_form`  set mystatus="done" where donecount>= mycount';


            $this->pdo->doSql($sql);


            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['myvalue'] = $a_form['myvalue'] / 100;
        $this->j['success'] = 'y';
    }

    /* 检测本页验证码是否正确 */

    function vertify() {
        $this->main->posttype = 'get';
        $codestr = $this->main->request('codestr', '答案', 1, 10, 'char');

        if (!$this->ckerr()) {
            return false;
        }

//接收到的激码和session对比
        if ($codestr != $_SESSION['codestr']) {
            $this->ckerr('答案错误!');
            return;
        }


        $this->j['success'] = 'y';
    }

    function sendsms() {
        $this->main->posttype = 'get';
        $u_mobile = $this->main->request('f_mobile', '手机号', 11, 11, 'mobile');


        if (!$this->ckerr()) {
            return false;
        }


        /* 提取手机激活码 */
        $cache = & $GLOBALS['cache'];


        $arrcode = $cache->get($u_mobile);




        /* 没提到缓存 */
        if (false != $arrcode) {

            $inttime = 60 - (time() - $arrcode['time']);
            if ($inttime < 60 && $inttime > 0) {
                $this->ckerr('请' . $inttime . '秒后再获取');
                return false;
            }
            $cache->delete($u_mobile); //删除缓存
        }


        /* 放进缓存 */
        $arrcode['code'] = $GLOBALS['main']->generate_randchar(6, 'num');
        $arrcode['time'] = time();

        $cache->save($u_mobile, $arrcode, 1800); //有效时间半小时

        /* 发送短信 */

        $c_sms = new cls_sms();

        $content = $c_sms->getsendmsg(35);
        $content = str_replace('{$activecode}', $arrcode['code'], $content);


        $para['uid'] = 0;
        $para['comid'] = 0;
        $result = $c_sms->send($u_mobile, $content, $para);


        if (false == $result) {
            $this->ckerr('短信发送失败');
            return false;
        }


        $this->j['success'] = 'y';
    }

    //检测是不是中文
    function isChineseName($name) {
        if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $name)) {
            return true;
        } else {
            return false;
        }
    }

}

$myapi = new myapi();
unset($myapi);

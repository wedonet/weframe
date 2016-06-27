<?php

/* api 用户注册 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once (syspath . '_inc/cls_sms.php');
require_once (syspath . '_inc/cls_user.php');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        //$this->modulemain = new cls_replenishment();
        //不是从登陆页进来的
        //$session = array('service/login.php');
        // if (!in_array($_SERVER['HTTP_REFERER'], $session)) {
        // $_SESSION[CacheName . 'procomeurl'] = $_SERVER['HTTP_REFERER'];              
        // }

        $this->mainpage();

        switch ($this->act) {
            case '':
                // $this->mainpage();
                break;
            case 'save':
                $this->saveinfor();
                $this->output();
                break;
            case 'vertify':
                $this->vertify();
                $this->output();
                break;
            case 'sendsms':
                $this->sendsms();
                $this->output();
                break;
            default :
                break;
        }
    }

    function mainpage() {
        $_SESSION[CacheName . 'HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
        // print_r( $_SESSION[CacheName.'HTTP_REFERER']);
        //print_r($_SERVER['HTTP_REFERER']);die;
        if (!strpos($_SESSION[CacheName . 'HTTP_REFERER'], 'service/login.php') && !strpos($_SESSION[CacheName . 'HTTP_REFERER'], 'service/reg.php') && !strpos($_SESSION[CacheName . 'HTTP_REFERER'], 'service/searchpass.php')) {
            $_SESSION[CacheName . 'procomeurl'] = $_SERVER['HTTP_REFERER'];
            //print_r($_SESSION[CacheName . 'procomeurl']);
            // die;
        }
        //真正的来时网址
        // print_r( $_SESSION[CacheName . 'procomeurl'] );die;
        //$_SESSION[CacheName . 'procomeurl'] = $_SERVER['HTTP_REFERER'];
    }

    function saveinfor() {
        $c_user = new cls_user();

        $we = & $GLOBALS['main'];

        $we->posttype = 'post';

        $u_mobile = $we->request('u_mobile', '手机号', 11, 11, 'mobile');
        $u_pass = $we->request('u_pass', '密码', 6, 20, 'char');
        $smscode = $we->request('smscode', '验证码', 1, 999999, 'num');
        
        
         //必须再次验证验证码
        $codestr = $this->main->request('codestr', '答案', 1, 10, 'char');
        if ($codestr != $_SESSION['codestr']) {
            $this->ckerr('答案错误!');
            return;
        }

        if (!$this->ckerr()) {
            return false;
        }


        /* 检测验证码对不对 */
        $arrcode = $GLOBALS['cache']->get($u_mobile);
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



        $rs['u_mobile'] = $u_mobile;
        $rs['u_pass'] = $u_pass;

        $rs['u_nick'] = substr($u_mobile, 0, 7) . $we->generate_randchar(4);

        $rs['u_name'] = $u_mobile; //用户名也存成手机号


        $result = $c_user->savenewuser($rs, 'user', 'sys');

        if (true == $result) {
            /* 删除缓存的验证码 */
            $GLOBALS['cache']->delete($u_mobile);

            $this->j['success'] = 'y';
            $this->j['msg'] = '注册成功';
       $c_user->checklogin('', $u_pass, 'user', '', $u_mobile);
            //从缓存中提取订单号，更新数据库中订单uid，将游客为0的uid更新真正uid（主要是未登陆的订单）
            if (isset($_SESSION[CacheName . 'orders'])) {
                $orders = $_SESSION[CacheName . 'orders'];
                $this->main->GetUserInfo();
                $rs['uid'] = $this->main->user['id'];
             
                // print_r($orders);die;
                $rs['ugic'] = $this->main->user['u_gic'];
                if ($rs['ugic'] == 'user') {
                    $sql = 'update ' . sh . '_order';
                    $sql.=' set uid=' . $rs['uid'];
                    $sql.=' where id in(' . $orders . ')';
                    $this->pdo->doSql($sql);
                }
            }
          
            
            $this->ckerr();
        } else {
            $this->j['success'] = 'n';
            $this->ckerr();
        }
    }

    /* 检测验证码是否正确 */

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

    /* 下一步还得防止一个ip反复发送,以上刷爆了 plan to do */

    function sendsms() {
        $this->main->posttype = 'get';
        $u_mobile = $this->main->request('u_mobile', '手机号', 11, 11, 'mobile');
        
        //必须再次验证验证码
         $codestr = $this->main->request('codestr', '答案', 1, 10, 'char');
         if ($codestr != $_SESSION['codestr']) {
            $this->ckerr('答案错误!');
            return;
        }


        if (!$this->ckerr()) {
            return false;
        }

        /* 检测这个手机号是否已经是会员了 */
        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and isdel=0 ';
        $sql .= ' and u_gic="user" '; //只检测会员重复性
        $sql .= ' and u_mobile=:u_mobile ';
        $counts = $this->pdo->counts($sql, Array(':u_mobile' => $u_mobile));
        if ($counts > 0) {
            $this->ckerr('这个手机号已经注册过了');
        }

        /* 提取手机激活码 */
        $cache = & $GLOBALS['cache'];


        /* arrcode['time'] = timeint
         * arrcode['code'] = '001234'
         * 
         *          */
        $arrcode = $cache->get($u_mobile);

        /* 没提到缓存 */
        if (false != $arrcode) {

              $inttime= 60-(time()-$arrcode['time']);
            if ($inttime < 60 && $inttime>0) {

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

        $content = $c_sms->getsendmsg(1);
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

}

$myapi = new myapi();
unset($myapi);

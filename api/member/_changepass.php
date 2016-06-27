<?php

/* 修改个人密码 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'member/_main.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->modulemain = new cls_modulemain();
        /* 跟据act确定输出格式 */
        $jsonact = array(
            'save'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }


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

    function pagemain() {
        
    }

    function saveinfor() {
        $this->main->posttype = 'post';
        $u_passold = $this->main->request('u_passold', '原密码', 6, 20, 'char');
        $u_pass = $this->main->request('u_pass', '新密码', 6, 20, 'char');
        $u_pass2 = $this->main->request('u_pass2', '确认密码', 6, 20, 'char');
        if (!$this->ckerr()) {
                return;
            }
        if ($u_pass !== $u_pass2) {
            if (!$this->ckerr('两次输入密码不同, 请重新输入')) {
                return;
            }
        }
        /* 提取原密码 */
        $uid = $this->main->user['id'];
        $sql = 'select * from `' . sh . '_user` where id=' . $uid;
        $result = $this->pdo->fetchOne($sql);
        if (false == $result) {
            $this->ckerr('没找到这个用户');
            return false;
        }
        if (md5($u_passold . $result['randcode']) != $result['u_pass']) {
            //ajaxerr('原密码错误!');
            $this->ckerr('原密码错误');
            return false;
        }

        $rs['u_pass'] = md5($u_pass . $result['randcode']);
        $result = $this->pdo->update(sh . '_user', $rs, 'id=' . $uid);

        $this->j['success'] = 'y';
    }

}

$myapi = new myapi();
unset($myapi);

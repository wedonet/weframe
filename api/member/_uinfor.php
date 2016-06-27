<?php

/* 修改个人信息 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'member/_main.php';
require_once syspath . '_inc/cls_user.php';

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
                $this->output('json');
                break;
        }
    }

    function pagemain() {
        
    }

    function saveinfor() {
        $this->main->posttype = 'post';
        $rs['u_nick'] = $this->main->request('u_nick', '用户昵称', 2, 20, 'char', 'encode', true);

        if (!$this->ckerr()) {
            return;
        }
           
        $sql = 'select count(*) from `' . sh . '_user` where 1 ';//---------------------------wenhui-------------
        
        $sql .= ' and id<>:id ';
        $sql .= ' and u_nick=:u_nick ';
       // print_r($sql);die;
        $para[':id'] = $this->main->user['id'];
        $para[':u_nick'] = $rs['u_nick'];//与接收过来的昵称做对比------------wenhui-

        // print_r($para);die;
        $counts = $this->pdo->counts($sql, $para);
        //print_r($counts);die;
         
        if ($counts > 0) {
           $this->ckerr('昵称已存在');
        }
        
 
        $uid = $this->main->user['id'];
        $count = $this->pdo->update(sh . '_user', $rs, 'id=' . $uid);
        $c_user = new cls_user();
        $result = $c_user->reloaduser($uid);
        
        if (true == $result) {

            $this->j['success'] = 'y';
        }
    }

}

$myapi = new myapi();
unset($myapi);

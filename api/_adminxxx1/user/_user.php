<?php

/* 会员接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';

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


        /* ============================== */

        switch ($this->act) {
            case '':
                $this->mylist();
                $this->output();
                break;
            case 'creat':
                $this->myform();
                $this->output();
                break;
            case 'nsave':
                $this->saveform();
                $this->output();
                break;
            case 'esave':
                $this->esave();
                $this->output();
                break;
            case 'savepass':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savepass();
                $this->output();
                break;
            case 'del': //
                $_POST['outtype'] = 'json'; //输出json格式
                $this->del();
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

                $this->doadmin();
                $this->output();
                break;
        }
    }

    /* 统计 */

    /* 用户列表 */

    function mylist() {
        /* 提取用户 */
        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and u_gic="user" ';
        $sql .= ' and isdel=0 ';
        $sql .= ' order by id desc ';

        $result = $this->main->exers($sql);
        $this->j['list'] = $result;
    }

    function myform() {
        
    }

    function saveform() {
        $we = & $this->main;
        $c_user = new cls_user();

        $we->posttype = 'post';

        $rs['u_mobile'] = $we->request('u_mobile', '手机', 11, 11, 'mobile');
        $rs['u_nick'] = $we->request('u_nick', '会员昵称', 2, 20, 'char', 'encode', false);
        $rs['u_pass'] = $we->request('u_pass', '密码', 6, 20, 'char');
        $u_pass2 = $we->request('u_pass2', '确认密码', 6, 20, 'char');

        $this->ckerr();
        /* 检测昵称是否存在 */
      if ($this->main->hasname(sh.'_user','u_nick',$rs['u_nick'],'',' and u_gic="user"')) {
            $this->ckerr('此昵称已经存在，请重新输入','u_name');
        }

        /* 检测两次输入密码是否一致 */
        if ($rs['u_pass'] !== $u_pass2) {
            $this->ckerr('两次输入密码不同, 请重新输入', 'u_pass2');
        }
        
         /* 检测手机是否存在 */
        if ($this->main->hasname(sh . '_user', 'u_mobile', $rs['u_mobile'],'', ' and u_gic="user"')) {
            $this->ckerr('您填写的手机号已经存在, 请重新填写', 'u_mobile');
        }

        if ('' == $rs['u_nick']) {
            $rs['u_nick'] = substr($rs['u_mobile'], 0, 7) . $we->generate_randchar(4);
        }
        $rs['u_name'] = $rs['u_mobile'];

        $result = $c_user->savenewuser($rs, 'user', 'user');

        if (true == $result) {
                /* 更新这个用户组的会员数 */
           // $sql = 'update `' . sh . '_group` set countuser= (select count(*) from ' . sh . '_user where u_gic="' . $u_gic . '") where ic="' . $u_gic . '"';
             $sql = 'update `' . sh . '_group` set countuser= (select count(*) from ' . sh . '_user where u_gic="user") where ic="user"';
         
             $this->pdo->doSql($sql);
            
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->j['success'] = 'n';
            $this->ckerr();
        }
    }

    function esave() {
        $we = & $GLOBALS['main'];

        $c_user = new cls_user();

        $id = $we->rfid();

        $we->posttype = 'post';

        $rs['u_nick'] = $we->request('u_nick', '会员昵称', 2, 20, 'char');
        $rs['u_mobile'] = $we->request('u_mobile', '手机', 6, 20, 'char', 'invalid');

        $this->ckerr();

        if ($this->main->hasname(sh . '_user', 'u_nick', $rs['u_nick'], $id, ' and u_gic="user"')) {
            $this->ckerr('此昵称已经存在，请重新输入', 'u_nick');
        }

        /* 检测手机是否存在 */
        if ($this->main->hasname(sh . '_user', 'u_mobile', $rs['u_mobile'], $id, ' and u_gic="user"')) {
            $this->ckerr('您填写的手机号已经存在, 请重新填写', 'u_mobile');
        }

        //最后一次修改人信息
        $rs['etimeint'] = time();
        $rs['etime'] = date('Y-m-d H:i:s', $rs['etimeint']);

        $rs['euid'] = $we->user['id'];
        $rs['enick'] = $we->user['u_nick'];


        $this->pdo->update(sh . '_user', $rs, ' id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    /* 修改密码 */

    function savepass() {
        $we = & $GLOBALS['main'];

        $id = $we->rfid();

        $we->posttype = 'post';

        $u_pass = $we->request('u_pass', '密码', 6, 20, 'char');
        $u_pass2 = $we->request('u_pass2', '确认密码', 6, 20, 'char');

        $this->ckerr();

        /* 检测密码是否一致 */
        if ($u_pass != $u_pass2) {
            $this->ckerr('两次输入密码不同, 请重新输入');
        }


        //密码处理
        $rs['randcode'] = $we->generate_randchar(8);
        $rs['u_pass'] = md5($u_pass . $rs['randcode']);

        $this->pdo->update(sh . '_user', $rs, 'id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    /* 提取用户 */

    function getuser() {
        $id = $this->main->rid();

        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and id=:id';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $this->j['thisuser'] = $result;
    }

//    function del() {
//        $id = $this->main->rqid();
//
//
//        /* 删除用户 */
//        $sql = 'delete from `' . sh . '_user` where 1 ';
//        $sql .= ' and u_gic="user" ';
//        $sql .= ' and id=:id';
//
//        $this->pdo->doSql($sql, Array(':id' => $id));
//        //更新用户组数量
//        $sql = 'update `' . sh . '_group` set countuser=countuser-1 where ic="user"';
//        $this->pdo->doSql($sql);
//
//        $this->j['success'] = 'y';
//    }

    /* 对用户的各种操作 */


    function doadmin() {
        $id = $this->main->rqid();

        /* 提取定单信息 */
        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and id=:id ';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $rs = Array();

        switch ($this->act) {
            case 'ischeck':
                if (1 == $result['ischeck']) {
                    $this->ckerr('已经审核通过了，不需要重复操作');
                } else {
                    $rs['ischeck'] = 1;
                }
                break;
            case 'uncheck':
                if (0 == $result['ischeck']) {
                    $this->ckerr('已经设为审核未通过了，不需要重复操作');
                } else {
                    $rs['ischeck'] = 0;
                }
                break;
            case 'islock':
                if (1 == $result['islock']) {
                    $this->ckerr('已经设为锁定了，不需要重复操作');
                } else {
                    $rs['islock'] = 1;
                }
                break;
            case 'unlock':
                if (0 == $result['islock']) {
                    $this->ckerr('已经设为解锁了，不需要重复操作');
                } else {
                    $rs['islock'] = 0;
                }
                break;
        }

        $this->pdo->update(sh . '_user', $rs, 'id=:id', Array(':id' => $id));


        $this->j['success'] = 'y';

//    if (1 == 1) {
//        $this->j['success'] = 'y';
//    } else {
//        $this->j['success'] = 'n';
//        $this->j['errmsg'][] = '错误1';
//        $this->j['errmsg'][] = '错误2';
//    }
    }

}

$myapi = new myapi();
unset($sys_admin_user);
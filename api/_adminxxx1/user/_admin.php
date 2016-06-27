<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
require_once ApiPath . '_adminxxx1/_main.php';
/* 返回用户组 */

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
                $this->mylist();
                $this->output();
                break;
            case 'creat':
                $this->myform();
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
            case 'del': //
                $_POST['outtype'] = 'json'; //输出json格式
                $this->del();
                $this->output();
                break;
        }
    }

    function mylist() {
        $sql = 'select * from `' . sh . '_user` where 1';
        $sql .= ' and u_gic="admin" ';
        //$sql .= ' and u_roleic="sys" '; //系统生成角色
        $sql .= ' order by id ';

        $this->j['userlist'] = $this->main->exers($sql);
    }

    function myform() {
        /* 提取管理员角色 */
        $sql = 'select id from `' . sh . '_group` where ic="admin"';
        $pid = $this->pdo->fetchOne($sql);

        $sql = 'select ic,title from `' . sh . '_group` where mytype="role" and pid=' . $pid['id'];
        $rs = $this->pdo->fetchAll($sql);
        $this->j['role'] = $rs;
    }

    /* 保存用户 */

    function esave() {
        $we = & $GLOBALS['main'];

        $c_user = new cls_user();

        $id = $we->rfid();

        $we->posttype = 'post';
        $rs['u_roleic'] = $we->request('u_roleic', '管理员角色', 2, 20, 'char', 'passstyle');
        $rs['u_nick'] = $we->request('u_nick', '昵称', 2, 20, 'char', 'encode');
        $rs['u_phone'] = $we->request('u_phone', '联系电话', 6, 20, 'phone', '', false);
        $rs['u_mobile'] = $we->request('u_mobile', '手机', 6, 20, 'mobile', '');
        $rs['u_mail'] = $we->request('u_mail', '电子邮箱', 6, 20, 'mail', '', false);

        if (!$this->ckerr()) {
            return false;
        }
        if ($this->main->hasname(sh . '_user', 'u_nick', $rs['u_nick'], $id, ' and u_gic="admin"')) {
            $this->ckerr('此昵称已经存在，请重新输入', 'u_nick');
        }

        /* 检测手机是否存在 */
        if ($c_user->hasmobile($rs['u_mobile'], $id, 'admin')) {
            $this->ckerr('您填写的手机号已经存在, 请重新填写', 'u_mobile');
        }

        $rs['u_rolename'] = $this->geturolename($rs['u_roleic']);

        //最后一次修改人信息
        $rs['etimeint'] = time();
        $rs['etime'] = date('Y-m-d H:i:s', $rs['etimeint']);

        $rs['euid'] = $we->user['id'];
        $rs['enick'] = $we->user['u_nick'];

        $this->pdo->update(sh . '_user', $rs, ' id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    function geturolename($ic) {
        $sql = 'select title from `' . sh . '_group` where 1 ';
        $sql .= 'and ic=:ic';

        $para[':ic'] = $ic;

        $result = $this->pdo->fetchOne($sql, $para);

        return $result['title'];
    }

    function savepass() {
        $we = & $GLOBALS['main'];

        $id = $we->rfid();

        $we->posttype = 'post';

        $u_pass = $we->request('u_pass', '密码', 6, 20, 'char', 'maxpwd');
        $u_pass2 = $we->request('u_pass2', '确认密码', 6, 20, 'char', 'maxpwd');

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

    /* 保存新管理员 */

    function savenew() {
        $we = & $GLOBALS['main'];
        $c_user = new cls_user();

        $we->posttype = 'post';
        $rs['u_roleic'] = $we->request('u_roleic', '管理员角色', 2, 20, 'char', 'passstyle');
        $rs['u_name'] = $we->request('u_name', '用户名', 6, 20, 'char', 'passstyle');
        $rs['u_nick'] = $we->request('u_nick', '昵称', 2, 20, 'char', 'encode');
        $rs['u_phone'] = $we->request('u_phone', '联系电话', 6, 20, 'phone', 'invalid', false);
        $rs['u_mobile'] = $we->request('u_mobile', '手机', 6, 20, 'mobile', '');
        $rs['u_mail'] = $we->request('u_mail', '电子邮箱', 6, 20, 'mail', '', false);

        $u_pass = $we->request('u_pass', '密码', 6, 20, 'char', 'maxpwd');
        $u_pass2 = $we->request('u_pass2', '确认密码', 6, 20, 'char', 'maxpwd');

        if (!$this->ckerr()) {
            return false;
        }



        /* 检测昵称是否重复 */
        if ($this->main->hasname(sh . '_user', 'u_nick', $rs['u_nick'], '', ' and u_gic="admin"')) {
            $this->ckerr('此昵称已经存在，请重新输入', 'u_nick');
        }


        /* 检测密码是否一致 */
        if ($u_pass != $u_pass2) {
            $this->ckerr('两次输入密码不同, 请重新输入');
        } else {
            $rs['u_pass'] = $u_pass;
        }

        if ($c_user->savenewuser($rs, 'admin', $rs['u_roleic'])) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->ckerr();
        }
    }

    /* 提取用户 */

    function getuser() {
        $id = $this->main->rid();

        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and id=:id';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $this->j['thisuser'] = $result;
        $this->myform();
    }

    function del() {
        $id = $this->main->rqid();
        $sql = 'select u_roleic from `' . sh . '_user` where 1 ';
        $sql .= ' and id=:id';
        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));
        if (false === $result) {
            $this->ckerr('没找到这个用户');
            return false;
        }
//        /* 检测是不是有店铺，有店铺先删除店铺 */
//        $sql = 'select count(*) from `' . sh . '_com` where 1 ';
//        $sql .= ' and uid=:id';
//
//        $counts = $this->pdo->counts($sql, Array(':id' => $id));
//
//        if ($counts > 0) {
//            /**/
//            $this->ckerr('请先删除这个用户的店铺，再删除这个用户');
//        }

        /* 删除用户 */
        $sql = 'delete from `' . sh . '_user` where 1 ';
        $sql .= ' and u_gic="admin" ';
        $sql .= ' and id=:id';

        $this->pdo->doSql($sql, Array(':id' => $id));
        //更新用户组数量  
        $sql = 'update `' . sh . '_group` set countuser= (select count(*) from ' . sh . '_user where u_gic="admin") where ic="admin"';

        $this->pdo->doSql($sql);

        $this->j['success'] = 'y';
        $this->j['msg'] = '保存成功';
        //更新角色组数量 
        $sql = 'update `' . sh . '_group` set countuser=countuser-1 where ic="' . $result['u_roleic'] . '"';
        $this->pdo->doSql($sql);

        $this->j['success'] = 'y';
    }

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

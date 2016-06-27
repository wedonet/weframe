<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
require_once AdminApiPath . '_main.php';
require_once '_main.php'; /* 业务管理通用数据 */

/* */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        $this->comid = $this->main->rid('comid');

        $c_company = new cls_companymain();

        /* 店铺信息添加进$globals['j']['company'] */
        $this->j['company'] = $c_company->getcompany($this->comid);

        $this->act = $this->main->ract();

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;
            case'select';
                $this->selectorder();
                $this->output();
                break;
            case'creat';
                $this->myform();
                $this->output();
                break;
            case 'edit':
                $this->formedit();
                $this->output();
                break;

            case 'nsave': //保存
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
            case 'del':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dodel();
                $this->output();
                break;
        }
    }

    function mylist() {
        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and u_gic="bizer" ';
        $sql .= ' and u_roleic<>"sys" ';
        $sql .= ' and comid=' . $this->comid;

        $result = $this->pdo->fetchAll($sql);

        $this->j['list'] = $result;
    }

    /* 提取平台的全设备 */

    function formedit() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_user` where id=:id';

        $this->j['data'] = $this->pdo->fetchOne($sql, Array(':id' => $id));
        $this->myform();
    }

    function myform() {
        $sql = 'select id from `' . sh . '_group` where ic="bizer"';
        $pid = $this->pdo->fetchOne($sql);

        $sql = 'select ic,title from `' . sh . '_group` where mytype="role" and pid=' . $pid['id'];
        $rs = $this->pdo->fetchAll($sql);

        $this->j['role'] = $rs;
    }

    function esave() {
        $we = & $GLOBALS['main'];

        $c_user = new cls_user();

        $id = $we->rfid();

        $we->posttype = 'post';

        $rs['u_fullname'] = $we->request('u_fullname', '真实姓名', 2, 20, 'char', 'ench');
        $rs['u_phone'] = $we->request('u_phone', '联系电话', 6, 20, 'phone', 'invalid', false);
        $rs['u_mobile'] = $we->request('u_mobile', '手机', 6, 20, 'mobile', 'invalid');
        $rs['u_mail'] = $we->request('u_mail', '电子邮箱', 6, 20, 'mail', '', false);
        $rs['u_roleic'] = $we->request('u_roleic', '角色', 1, 20, 'char', 'invalid');
        $rs['islock'] = $we->rfid('islock');
        $this->ckerr();

        //获取roleic对应的rolename
        $sql = 'select title from `' . sh . '_group` where 1 ';
        $sql .= ' and ic="' . $rs['u_roleic'] . '"';
        $result = $this->pdo->fetchOne($sql);
        $rs['u_rolename'] = $result['title'];
        //stop($result,true);
        /* 检测手机是否存在 */
        if ($c_user->hasmobile($rs['u_mobile'], $id, 'bizer')) {
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

    function savenew() {
        $we = & $GLOBALS['main'];

        $c_user = new cls_user();

        $we->posttype = 'post';

        $rs['u_name'] = $we->request('u_name', '用户名', 6, 20, 'char', 'passstyle');
        $rs['u_fullname'] = $we->request('u_fullname', '真实姓名', 2, 20, 'char', 'ench');
        $rs['islock'] = $we->rfid('islock');
        $rs['u_phone'] = $we->request('u_phone', '联系电话', 6, 20, 'phone', 'invalid', false);
        $rs['u_mobile'] = $we->request('u_mobile', '手机', 6, 20, 'mobile', 'invalid');
        $rs['u_mail'] = $we->request('u_mail', '电子邮箱', 6, 20, 'mail', '', false);

        $u_pass = $we->request('u_pass', '密码', 6, 20, 'char', 'maxpwd');
        $u_pass2 = $we->request('u_pass2', '确认密码', 6, 20, 'char', 'maxpwd');

        $u_roleic = $we->request('u_roleic', '角色', 1, 20, 'char', 'invalid');
        $rs['u_roleic'] = $u_roleic;
        //stop($u_roleic);
        $this->ckerr();

        /* 检测密码是否一致 */
        if ($u_pass != $u_pass2) {
            $this->ckerr('两次输入密码不同, 请重新输入');
        } else {
            $rs['u_pass'] = $u_pass;
        }

        /* 检测角色 */
        if (strpos('replenishment', $u_roleic) < 0) {
            $this->ckerr('角色错误!');
        }

        if ($c_user->savenewuser($rs, 'bizer', $u_roleic, $this->comid)) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->ckerr();
        }
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

    function dodel() {
        $id = $this->main->rqid();
        $sql = 'select u_roleic from `' . sh . '_user` where 1 ';
        $sql .= ' and id=:id';
        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));
        if (false === $result) {
            $this->ckerr('没找到这个用户');
            return false;
        }
         unset($sql);
        /* 删除用户 */
        $sql = 'delete from `' . sh . '_user` where 1 ';
        $sql .= ' and u_gic="bizer" ';
        $sql .= ' and id=:id';

        $this->pdo->doSql($sql, Array(':id' => $id));
         unset($sql);
         //更新用户组数量  
        $sql = 'update `' . sh . '_group` set countuser= (select count(*) from ' . sh . '_user where u_gic="bizer") where ic="bizer"';

        $this->pdo->doSql($sql);

        $this->j['success'] = 'y';
        $this->j['msg'] = '保存成功';
        //更新角色组数量 
        $sql = 'update `' . sh . '_group` set countuser=countuser-1 where ic="' . $result['u_roleic'] . '"';
        $this->pdo->doSql($sql);

        $this->j['success'] = 'y';
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
<?php

/* 角色接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once AdminApiPath . '_main.php';

/* 返回角色 */

class admin_user_role extends cls_api {

    function __construct() {
        parent::__construct();

        
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
       
        $this->pid = $this->main->rid('pid');

        if ($this->pid < 0) {
            $this->ckerr(1022);
        }

        switch ($this->main->ract()) {
            case '':
                $this->j['rolelist'] = $this->mylist();
                $this->output();
                break;
            case 'creat':
                $this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getrole();
                $this->output();
                break;
            case 'accessedit':
                $this->accessform();
                $this->output();
                break;
            case 'accessesave':
                 $_POST['outtype'] = 'json'; //输出json格式
                $this->accessesave();
                $this->output();
                break;

            case 'nsave': //保存新用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->saveform();
                $this->output();
                break;
            case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;
            case 'del': //删除用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->del();
                $this->output();
                break;
        }
    }

    function mylist() {
        $sql = 'select * from ' . sh . '_group where 1 ';
        $sql .= ' and mytype="role" ';
        $sql .= ' and pid=:pid';
        $sql .= ' order by cls asc,id asc ';

        return $this->pdo->fetchAll($sql, Array(':pid' => $this->pid));
    }
    function accessform(){ 
       
        $sql="select * from ".sh."_access ";
        $sql .= ' order by cls desc ,id asc';
        $access=$this->pdo->fetchAll($sql);
        $accesslist = array();
        foreach ($access as $key => $value) {
            if ($value['pid'] == 0) {
                $key = $value['id'];
                $accesslist[$key] = $value;
            }
        }
        foreach ($access as $key => $value) {
            if ($value['pid'] != 0) {
                $key = $value['pid'];
                $accesslist[$key]['son'][] = $value;
            }
        }
       $id = $this->main->rqid('id');
       $pid = $this->main->rqid('pid');
       $sql="select access_id from ".sh."_group where id=:id";
       $para[':id']=$id;
       $res=$this->pdo->fetchOne($sql,$para);
       $group_access=  explode("|", $res['access_id']);         
        $this->j['access'] = $accesslist;
        $this->j['group_access'] = $group_access;
        $this->j['id'] = $id;
        $this->j['pid'] = $pid;
    }
    function accessesave(){
         $we = & $GLOBALS['main'];
        $we->posttype = 'post';
        $id = $this->main->rfid('id');
        if ($id<1) {
            $this->ckerr("参数错误！");
        }
        $rs['access_id']=  implode('|', $_POST['access']);
        $t = $this->pdo->update(sh . '_group', $rs, 'id=:id', Array(':id' => $id));
        $this->j['success'] = 'y';
    }
            
    function myform() {
        
    }

    function saveform() {
        $we = & $GLOBALS['main'];

        $we->posttype = 'post';

        $rs['title'] = $we->request('title', '名称', 1, 50, 'char', 'encode');
        $rs['ic'] = $we->request('ic', '识别码', 1, 20, 'loginpass');
        $rs['cls'] = $we->rfid('cls', 100);
        $rs['isuse'] = $we->rfid('isuse', 1);

        $this->ckerr();

        $rs['mytype'] = 'role';

         //检测店铺名称
        if ($this->main->hasname(sh.'_group','title',$rs['title'])) {
            $this->ckerr('此角色名称已经存在，请重新输入','title');
        }

        /* 检测编号有重复 */
        if ($we->hasic(sh . '_group', -1, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }

        $rs['countuser'] = 0;
        $rs['countson'] = 0;
        $rs['pid'] = $this->pid;

        /* 添加角色,增加上级数量 */
        // try {
        //$we->pdo->begintrans();

        $we->pdo->insert(sh . '_group', $rs);

        /* 提取下级量 */
        $sql = 'select count(*) from `' . sh . '_group` where pid=:pid';
        $counts = $this->pdo->counts($sql, Array(':pid' => $this->pid));

        /* 更新用户组下级数量 */
        $sql = 'update `' . sh . '_group` set countson=' . $counts . ' where id=:pid';

        $t = $we->pdo->doSql($sql, Array(':pid' => $this->pid));

        //    $we->pdo->submittrans();
        // } catch (PDOException $e) {
        //    $we->pdo->rollbacktrans();
        //    $this->ckerr($e);
        //    return false;
        //}

        $we->deletecache('group');

        $this->j['success'] = 'y';
    }

    function esave() {
        $we = & $GLOBALS['main'];

        //接收参数
        $id = $we->rfid('id');

        $we->posttype = 'post';

        $rs['title'] = $we->request('title', '名称', 1, 50, 'char', 'encode');
        $rs['ic'] = $we->request('ic', '识别码', 1, 20, 'loginpass');
        $rs['cls'] = $we->rfid('cls', 100);
        $rs['isuse'] = $we->rfid('isuse', 1);

        $this->ckerr();

        if ($this->main->hasname(sh.'_group','title',$rs['title'],$id)) {
            $this->ckerr('此角色名称已经存在，请重新输入','title');
        }

        /* 检测编号有重复 */
        if ($we->hasic(sh . '_group', $id, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }


        /* 编辑时，如果更改了编码，then更新用户表里原编码 */
        $sql = 'select ic from `' . sh . '_group` where id=:id';
        $a_role = $we->pdo->fetchOne($sql, Array(':id' => $id));
        if (false == $a_role) {
            $this->ckerr(1018);
        }

        $OriginalIc = $a_role['ic'];


        /* 更新用户表 */
        $u['u_roleic'] = $rs['ic'];
        $u['u_rolename'] = $rs['title'];

        //print_r($rs);break;
        try {
            $we->pdo->begintrans();
            $t = $we->pdo->update(sh . '_group', $rs, 'id=:id', Array(':id' => $id));
            $t = $we->pdo->update(sh . '_user', $u, 'u_roleic=:u_roleic', Array(':u_roleic' => $OriginalIc));
            $we->pdo->submittrans();
        } catch (PDOException $e) {
            $we->pdo->rollbacktrans();
            $this->ckerr($e);
            return false;
        }

        $we->deletecache('group');

        $this->j['success'] = 'y';
    }

    function del() {
        $id = $this->main->rqid('id');
        $pid = $this->main->rqid('pid');
        /* 提取这个组 */
        $sql = 'select * from `' . sh . '_group` where 1 ';
        $sql .= ' and id=:id';
        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        if (false == $result) {
            $this->ckerr('要删除的角色不存在');
        }

        /* 提取用户角色ic */
        $u_roleic = $result['ic'];


        $sql = 'select count(*) from `' . sh . '_user` where u_roleic=:u_roleic';
        $counts = $this->pdo->counts($sql, Array(':u_roleic' => $u_roleic));
        if ($counts > 0) {
            $this->ckerr('该角色尚有用户，无法进行删除！');
        }

        $sql = 'delete from ' . sh . '_group where id=:id';

        $result = $this->pdo->del($sql, Array(':id' => $id));
        /* 更新用户组下级数量 */
        $sql = 'update `' . sh . '_group` set countson=countson-1 where id=:pid';
        $t = $this->pdo->doSql($sql, Array(':pid' => $pid));
        if (false != $result) {
            $this->j['success'] = 'y';
            $this->main->deletecache('group');
        }
    }

    function getrole() {
        $id = $this->main->rqid('id');

        $sql = 'select * from `' . sh . '_group` where id=:id';

        $this->j['role'] = $this->pdo->fetchOne($sql, Array(':id' => $id));
    }

}

$admin_user_role = new admin_user_role();
unset($admin_user_role);
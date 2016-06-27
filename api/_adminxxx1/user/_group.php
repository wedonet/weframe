<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once AdminApiPath . '_main.php';

/* 返回用户组 */

class admin_user_group extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
        
        
        switch ($this->main->ract()) {
            case '':
                $this->j['grouplist'] = $this->mylist();
                $this->output();
                break;
            case 'creat':
                //$this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getgroup();
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
        }
    }

    function mylist($field = '*') {
        $sql = 'select ' . $field . ' from ' . sh . '_group where 1 ';
        $sql .= ' and mytype = "group" ';
        $sql .= ' order by cls asc,id asc ';

        return $this->pdo->fetchAll($sql);
    }

    function saveform() {
        $we = & $GLOBALS['main'];

        $we->posttype = 'post';

        $rs['title'] = $we->request('title', '名称', 1, 50, 'char', 'encode');
        $rs['ic'] = $we->request('ic', '识别码', 1, 20, 'loginpass');
        $rs['cls'] = $we->rfid('cls', 100);
        $rs['isuse'] = $we->rfid('isuse', 1);

        $this->ckerr();

        $rs['mytype'] = 'group';

         //检测店铺名称
        if ($this->main->hasname(sh.'_group','title',$rs['title'])) {
            $this->ckerr('此用户组名称已经存在，请重新输入','title');
        }

        /* 检测编号有重复 */
        if ($we->hasic(sh . '_group', -1, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }

        $rs['countuser'] = 0;
        $rs['countson'] = 0;

        $id = $this->pdo->insert(sh . '_group', $rs);

        $this->j['success'] = 'y';


        $we->deletecache('group');
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

         //检测店铺名称
        if ($this->main->hasname(sh.'_group','title',$rs['title'],$id,' and mytype="group"')) {
            $this->ckerr('此用户组名称已经存在，请重新输入','title');
        }

        /* 检测编号有重复 */
        if ($we->hasic(sh . '_group', $id, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }


        /* 编辑时，如果更改了编码，then更新用户表里原编码 */
        $sql = 'select ic from `' . sh . '_group` where id=:id';
        $a_group = $we->pdo->fetchOne($sql, Array(':id' => $id));
        if (false == $a_group) {
            $this->ckerr(1018);
        }

        $OriginalIc = $a_group['ic'];


        /* 更新用户表 */
        $u['u_gic'] = $rs['ic'];
        $u['u_gname'] = $rs['title'];

        //print_r($rs);break;
        try {
            $we->pdo->begintrans();
            $t = $we->pdo->update(sh . '_group', $rs, 'id=:id', Array(':id' => $id));
            $t = $we->pdo->update(sh . '_user', $u, 'u_gic=:u_gic', Array(':u_gic' => $OriginalIc));
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
        
        /*提取这个组*/
        $sql = 'select * from `'.sh.'_group` where 1 ';
        $sql .= ' and id=:id';
        $result = $this->pdo->fetchOne($sql, Array(':id'=>$id));
        
        if(false == $result ){
            $this->ckerr('要删除的用户组不存在');
        }
        
        /*提取用户组ic*/
        $u_gic = $result['ic'];

        /*检测这个用户组不能有会员*/
        $sql = 'select count(*) from `'.sh.'_group` where pid=:id';
        $counts = $this->pdo->counts($sql, Array(':id'=>$id) );
        if($counts>0){
            $this->ckerr('请先删除这个用户组下的角色！');
        }
        
        $sql = 'select count(*) from `'.sh.'_user` where u_gic=:u_gic';
        $counts = $this->pdo->counts($sql, Array(':u_gic'=>$u_gic) );
        if($counts>0){
            $this->ckerr('把先把这个组的用户转至其它组再进行删除！');
        }       
        
        $sql = 'delete from ' . sh . '_group where id=:id';

        $result = $this->pdo->del($sql, Array(':id'=>$id) );

        if (false != $result) {
            $this->j['success'] = 'y';
            $this->main->deletecache('group');
        }
    }

    function getgroup() {
        $id = $this->main->rqid('id');

        $sql = 'select * from `' . sh . '_group` where id=:id';

        //$para[':id'] = $id;

        $this->j['group'] = $this->pdo->fetchOne($sql, Array(':id' => $id));
    }

}

$admin_user_group = new admin_user_group();
unset($admin_user_group);
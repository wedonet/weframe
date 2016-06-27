<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
require_once ApiPath . '_adminxxx1/_main.php';
/* 返回用户组 */

class access_mange extends cls_api {

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
            case 'nsave':
                $this->nsave();
                $this->output();
                break;
            case 'edit':
                $this->edit();
                $this->output();
                break;
            case 'esave':
                $this->esave();
                $this->output();
                break;
            case 'del':
                $this->del();
                $this->output();
                break;
        }
    }

    //列表
    function mylist() {
        $sql = 'select * from `' . sh . '_access`';
        //$sql .= ' and u_roleic="sys" '; //系统生成角色
        $sql .= ' order by cls desc ,id asc';
        $access = $this->pdo->fetchAll($sql);
//       print_r($access);
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

        $this->j['accesslist'] = $accesslist;
    }

//    新增
    function myform() {
        $sql = 'select * from `' . sh . '_access` where pid=0';
        $rs = $this->pdo->fetchAll($sql);
        $this->j['access_type'] = $rs;
    }

//处理新增
    function nsave() {
        $we = & $GLOBALS['main'];
        $_POST['outtype'] = 1;
        $we->posttype = 'post';
        $rs = [];
        $rs['pid'] = $we->request('pid', '分类', 0, 100, 'char', '', true);
        $rs['name'] = $we->request('name', '名称', 0, 100, 'char', 'encode', true);
        $rs['name']=trim($rs['name']);
        $rs['title'] = $we->request('title', '描述', 0, 200, 'char', 'encode');
        $rs['cls'] = $we->request('cls', '排序', 0, 2000, 'num', '', FALSE);
        if (!$this->ckerr()) {
            return false;
        }
        $sql = "select id from " . sh . "_access where name=:name";
        $para[':name'] = $rs['name'];
        $res = $this->pdo->fetchOne($sql, $para);
        if ($res) {
            $this->ckerr("名称不能重复");
            return;
        }
        $id = $this->pdo->insert(sh . '_access', $rs);
        if ($id) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->ckerr();
        }

//         $this->j['success'] = 'y';
    }

//    编辑
    function edit() {
        $we = & $GLOBALS['main'];

        $sql = 'select * from `' . sh . '_access` where pid=0';
        $rs = $this->pdo->fetchAll($sql);
        $this->j['access_type'] = $rs;
        $id = $we->rqid();
        $sql = 'select * from `' . sh . '_access` where id=:id';
        $para[':id'] = $id;
        $rs = $this->pdo->fetchOne($sql, $para);
        if (empty($rs)) {
            $this->ckerr("参数错误！");
            return ;
        }
        $this->j['access_info'] = $rs;
    }

    function esave() {
        $we = & $GLOBALS['main'];
        $_POST['outtype'] = 1;
        $we->posttype = 'post';
        $id = $we->rfid();
        if($id<1){
            $this->ckerr("参数错误！");
            return ;
        }         
        $rs = [];
        $rs['pid'] = $we->request('pid', '分类', 0, 100, 'char', '', true);
        $rs['name'] = $we->request('name', '名称', 0, 100, 'char', 'encode', true);
         $rs['name']=trim($rs['name']);
        $rs['title'] = $we->request('title', '描述', 0, 200, 'char', 'encode');
        $rs['cls'] = $we->request('cls', '排序', 0, 2000, 'num', '', FALSE);
        if (!$this->ckerr()) {
            return false;
        }
        $sql = "select id from " . sh . "_access where name=:name and id !=".$id;
        $para[':name'] = $rs['name'];
        $res = $this->pdo->fetchOne($sql, $para);
        if ($res) {
            $this->ckerr("名称不能重复");
            return;
        }
        $this->pdo->update(sh . '_access', $rs, ' id=:id', Array(':id' => $id));
        $this->j['success'] = 'y';
    }

    function del() {
         $_POST['outtype'] = 'json';
        $id = $this->main->rqid();        
        $sql = 'select id from `' . sh . '_access` where 1 ';
        $sql .= ' and id=:id';
        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));
        if (false === $result) {
            $this->ckerr('请求错误！');
            return false;
        }
        /* 删除用户 */
        $sql = 'delete from `' . sh . '_access` where 1 ';
        $sql .= ' and id=:id';
        $this->pdo->doSql($sql, Array(':id' => $id)); 
        $this->j['success'] = 'y';
        $this->j['msg'] = '删除成功';
        

    }

}

$access_mange = new access_mange();
unset($access_mange);

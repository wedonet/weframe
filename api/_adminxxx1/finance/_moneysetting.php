<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
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
			
        $this->act = $this->main->ract();

        switch ($this->act) {
            case '':
                $this->mylist();
                $this->output();
                break;
            case 'edit':
                $this->getdata();
                $this->output();
                break;
            case 'editnext':
                $this->getdata();
                $this->output();
                break;

            case 'nsave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->nsave();
                $this->output();
                break;

            case 'nsavenext':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->nsavenext();
                $this->output();
                break;

            case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;

            case 'esavenext':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esavenext();
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
        $sql = 'select * from `' . sh . '_moneysetting` where 1 ';
        $sql .= ' order by cls asc, id asc ';


        $result = $this->pdo->fetchAll($sql);

        $this->j['list'] = $result;
    }

    function nsave() {
        $id = $this->main->rfid();

        $this->main->posttype = 'post';
        $rs['title'] = $this->main->request('title', '名称', 1, 20, 'char', 'encode');
        $rs['ic'] = $this->main->request('ic', '编码', 1, 20, 'char', 'invalid');
        $rs['cls'] = $this->main->rfid('cls');

        $this->ckerr();

        $rs['pid'] = 0;

        /*检测名称是否重复*/
        if ($this->main->hasname(sh. '_moneysetting','title',$rs['title'])) {
            $this->ckerr('此名称已经存在，请重新输入','title');
        }

        /* 检测编号有重复 */
        if ($this->main->hasic(sh . '_moneysetting', $id, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }

        $id = $this->pdo->insert(sh . '_moneysetting', $rs);

        $this->j['success'] = 'y';
    }

    function esave() {
        $id = $this->main->rfid();

        $this->main->posttype = 'post';

        $rs['title'] = $this->main->request('title', '名称', 1, 20, 'char', 'encode');
        $rs['ic'] = $this->main->request('ic', '编码', 1, 20, 'char', 'invalid');
        $rs['cls'] = $this->main->rfid('cls');

        $this->ckerr();

        /*检测名称是否重复*/
        if ($this->main->hasname(sh.'_moneysetting','title',$rs['title'],$id)) {
            $this->ckerr('此名称已经存在，请重新输入','title');
        }

        /* 检测编号有重复 */
        if ($this->main->hasic(sh . '_moneysetting', $id, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }

        $t = $this->pdo->update(sh . '_moneysetting', $rs, 'id=:id', Array(':id' => $id));



        $this->j['success'] = 'y';
    }

    function nsavenext() {
        $id = $this->main->rfid();

        $this->main->posttype = 'post';

        $rs['title'] = $this->main->request('title', '名称', 1, 20, 'char', 'encode');
        $rs['ic'] = $this->main->request('ic', '编码', 1, 20, 'char', 'invalid');

        $rs['cls'] = $this->main->rfid('cls');
        $rs['pid'] = $this->main->rfid('pid');

        $rs['opuser'] = $this->main->request('opuser', '对个人操作', 1, 20, 'char', '');
        $rs['opbiz'] = $this->main->request('opbiz', '对商家操作', 1, 20, 'char', '');
        $rs['opplat'] = $this->main->request('opplat', '对平台操作', 1, 20, 'char', '');

        $this->ckerr();

         /*检测名称是否重复*/
        if ($this->main->hasname(sh.'_moneysetting','title',$rs['title'])) {
            $this->ckerr('此名称已经存在，请重新输入','title');
        }

        /* 检测编号有重复 */
        if ($this->main->hasic(sh . '_moneysetting', $id, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }

        $id = $this->pdo->insert(sh . '_moneysetting', $rs);

        $this->j['success'] = 'y';
    }

    function esavenext() {
        $id = $this->main->rfid();

        $this->main->posttype = 'post';

        $rs['title'] = $this->main->request('title', '名称', 1, 20, 'char', 'encode');
        $rs['ic'] = $this->main->request('ic', '编码', 1, 20, 'char', 'invalid');

        $rs['cls'] = $this->main->rfid('cls');


        $rs['opuser'] = $this->main->request('opuser', '对个人操作', 1, 20, 'char', '');
        $rs['opbiz'] = $this->main->request('opbiz', '对商家操作', 1, 20, 'char', '');
        $rs['opplat'] = $this->main->request('opplat', '对平台操作', 1, 20, 'char', '');

        $this->ckerr();
           
         /*检测名称是否重复*/
        if ($this->main->hasname(sh.'_moneysetting','title',$rs['title'],$id)) {
            $this->ckerr('此名称已经存在，请重新输入','title');
        }


        /* 检测编号有重复 */
        if ($this->main->hasic(sh . '_moneysetting', $id, $rs['ic'])) {
            $this->ckerr('识别码重复，请重新输入', 'ic');
        }

        $t = $this->main->pdo->update(sh . '_moneysetting', $rs, 'id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    function getdata() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_moneysetting` where id=' . $id;


        $this->j['data'] = $this->pdo->fetchOne($sql);
    }

    function dodel(){
        $id=$this->main->rqid();
        
        
        
        /*检测有没有下级*/
        $sql = 'select count(*) from `'.sh.'_moneysetting` where 1 ';
        $sql .= ' and pid=:id';
        
        $mycounts = $this->pdo->counts($sql, Array(':id'=>$id));
   
        if($mycounts>0){
            $this->j['success'] = 'n';
            $this->ckerr('还有下级了，不能删除，请先删除下级');
        }
        
        /*删除*/
        $sql = 'delete from `'.sh.'_moneysetting` where id=:id ';
        
        $this->pdo->del($sql, Array(':id'=>$id));
        
        $this->j['success'] = 'y';
    }
}

$myapi = new myapi();
unset($sys_admin_user);
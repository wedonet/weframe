<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
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
            case'creat';
                $this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getplace();
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
            case 'del':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dodel();
                $this->output();
                break;
        }
    }

    function mylist() {
        $sql = 'select * from `' . sh . '_place` where 1 ';
        $sql .= ' and comid=:comid';
        $sql .= ' order by cls asc,id asc ';

        $result = $this->pdo->fetchAll($sql, Array(':comid' => $this->comid));

        $this->j['list'] = & $result;
    }

    /* 提取平台的全部柜门位置 */

    function formedit() {
        
    }

    function myform() {
        
    }

    function getplace() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_place` where 1 ';
        $sql .= ' and id=:id';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $GLOBALS['j']['data'] = & $result;
    }

    function esave() {
        $id = $this->main->rfid();

        $this->main->posttype = 'post';

        $rs['ic'] = $this->main->request('ic', 'IC', 2, 20, 'char', 'invalid');
        $rs['title'] = $this->main->request('title', '名称', 1, 20, 'char', 'invalid');
        $rs['building'] = $this->main->request('building', '栋', 1, 20, 'char', 'invalid');
        $rs['floor'] = $this->main->request('floor', '层', 1, 200, 'int');

        $rs['cls'] = $this->main->request('cls', '排序', 0, 9999999, 'int');

        $this->ckerr();

        /* 检测重复ic */
        if ($this->main->hasic(sh . '_place', $id, $rs['ic'])) {
            $this->ckerr('IC重复');
        }




        $this->pdo->update(sh . '_place', $rs, 'id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    function savenew() {
        $this->main->posttype = 'post';

        $rs['ic'] = $this->main->request('ic', 'IC', 2, 20, 'char', 'invalid');
        $rs['title'] = $this->main->request('title', '名称', 1, 20, 'char', 'invalid');
        $rs['building'] = $this->main->request('building', '栋', 1, 20, 'char', 'invalid');
        $rs['floor'] = $this->main->request('floor', '层', 1, 200, 'int');

        $rs['cls'] = $this->main->request('cls', '排序', 0, 9999999, 'int');

        $this->ckerr();

        /* 检测重复ic */
        if ($this->main->hasic(sh . '_place', -1, $rs['ic'])) {
            $this->ckerr('IC重复');
        }

        $rs['comid'] = $this->comid;

        /* 入库 */
        $id = $this->pdo->insert(sh . '_place', $rs);

        $this->j['success'] = 'y';
    }

    function dodel() {
        $id = $this->main->rqid(); 

        /* 检测这个铺位有没有设备 */
        $sql = 'select count(*) from `'.sh.'_device` where 1 ';
        $sql .= ' and comid=:comid';
        $sql .= ' and placeid=:id';
        $counts = $this->pdo->counts($sql, Array(':comid'=>$this->comid, ':id'=>$id));
        if($counts>0){
            $this->ckerr('这个位置还有设备了，请先把设备移出这个铺位');
        }
        
        /*删除*/
        $sql = 'delete from `'.sh.'_place` where id=:id';
        $this->pdo->doSql($sql, Array(':id'=>$id) );
        
        $this->j['success'] = 'y';
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
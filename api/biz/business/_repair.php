<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'biz/_main.php';

/**
 * 
 */
class admin_business_repairhistory extends cls_api {

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
                $this->doorlist();
                $_POST['outtype'] = 'json'; //输出json格式	
                $this->output();
                break;
            default:
                break;
        }
    }

    function mylist() {

//        $sql = 'select failtofix.* ';
//        $sql .= ' ,goods.title as goodstitle ';
//        $sql .= ' ,door.id as doorid, door.deviceid as doordeviceid ';
//        $sql .= ' from `' . sh . '_failtofix` as failtofix  ';
//
//        $sql .= ' left join `' . sh . '_door` as door ';
//        $sql .= ' on failtofix.comid=door.comid and failtofix.door=door.title and failtofix.placeid=door.placeid ';
//
//        $sql.='  left join `' . sh . '_goods` as goods ';
//        $sql .= ' on door.goodsid =goods.id';
//        $sql .= ' where 1 ';
//        $sql .= ' and failtofix.isend=0 ';
//        $sql .= ' and failtofix.type!="心跳包异常" ';
//        $sql .= ' and failtofix.comid=:comid ';
//        $sql .= ' order by failtofix.id desc';
//        
//        $para[':comid'] = $this->main->user['comid'];
//        
         $sql = 'select failtofix.* ';
        $sql .= ' from `' . sh . '_failtofix` as failtofix  ';       
        $sql .= ' where 1 ';
        $sql .= ' and failtofix.isend=0 ';
        $sql .= ' and failtofix.mytype!="heart" ';
        $sql .= ' and failtofix.comid=:comid ';
        $sql .= ' order by failtofix.id desc';
        
        $para[':comid'] = $this->main->user['comid'];
        
        //print_r($sql);die;
        $rs = $this->main->exers($sql, $para);
        $this->j['list'] = $rs;
    }

    function doorlist() {
        $id = $this->main->rqid('doorid');
        $doordeviceid = $this->main->rqid('doordeviceid');
        $failtofixid = $this->main->rqid('failtofixid');
        //print_r($doordeviceid);die;
        if (!$this->ckerr()) {
            return false;
        }
        //门状态变成已送货，是否换过货（）
        $sql = '  update `' . sh . '_door` ';
        $sql .= '  set ischange=1 ,hasgoods=1';
        $sql .= ' where id=' . $id;
        //print_r($sql);die;
        // $quar[':id']=$id;
        $orid = $GLOBALS['pdo']->doSql($sql);
        //$GLOBALS['pdo']->doSql($sql,$quar);
        unset($sql);
        //更改设备里商品个数
        $sql = '  update `' . sh . '_device` ';
        $sql .= '  set goodsnum=goodsnum+1';
        $sql .= ' where id=' . $doordeviceid;
        //print_r($sql);die;
        // $quar[':id']=$id;
        $orid = $GLOBALS['pdo']->doSql($sql);
        //将设备维修表中酒店送货字段变为1
        unset($sql);

        $sql = '  update `' . sh . '_failtofix` ';
        $sql .= '  set ischange=1';
        $sql .= ' where id=' . $failtofixid;
        //print_r($sql);die;
        // $quar[':id']=$id;
        $orid = $GLOBALS['pdo']->doSql($sql);
        $this->j['success'] = 'y';
        // print_r('111111');die;
    }

}

$admin_business_repairhistory = new admin_business_repairhistory;
unset($admin_business_repairhistory);

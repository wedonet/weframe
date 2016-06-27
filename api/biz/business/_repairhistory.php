<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'biz/_main.php';
require_once syspath . '_inc/cls_user.php';

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

            default:
                break;
        }
    }

    function mylist() {


//        $sql = 'select failtofix.*,goods.title as goodstitle,door.id as doorid from `' . sh . '_failtofix` as failtofix  ';
//
//        $sql.='  left join `' . sh . '_door` as door ';
//        $sql .= ' on failtofix.comid=door.comid and failtofix.door=door.title and failtofix.placeid=door.placeid';
//
//        $sql.='  left join `' . sh . '_goods` as goods ';
//        $sql .= ' on door.goodsid =goods.id';
//        $sql .= ' where failtofix.isend=1 and failtofix.type!="心跳包异常"';
//        $sql .= ' and failtofix.comid=:comid ';
//        $sql .= ' order by failtofix.id desc';
//        
//
//        $para[':comid'] = $this->main->user['comid'];
 $sql = 'select failtofix.*  from `' . sh . '_failtofix` as failtofix  ';      
        $sql .= ' where failtofix.isend=1 and failtofix.mytype!="heart"';
        $sql .= ' and failtofix.comid=:comid ';
        $sql .= ' order by failtofix.id desc';
        

        $para[':comid'] = $this->main->user['comid'];
        $rs = $this->main->exers($sql, $para);
        $this->j['list'] = $rs;
    }

}

$admin_business_repairhistory = new admin_business_repairhistory;
unset($admin_business_repairhistory);

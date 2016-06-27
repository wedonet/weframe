<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once AdminApiPath . '_main.php';

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
            case 'export':
                $this->mylist();
                $this->output();
                break;
        }
    }

    /* 用户列表 */

    function mylist() {

        $this->posttype = 'get';
        $comid = $this->main->rqid("comid");
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);
//        print_r($comid );
//        print_r($date1 );
//        print_r($date2 );die;
        //传回前端
        if ($comid == -1) {
            $comid = '';
        }
        $this->j['search']['comid'] = $comid;
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['comname'] = '';

        if (!$this->ckerr()) {
            return false;
        }
        /* check com */
        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }
        /* check date */
        if ('' == $date1) {
            $date1_int = strtotime(date('Y-m-d', (time() - 7 * 24 * 3600)));
        } else {
            $date1_int = strtotime($date1);
        }

        if ('' == $date2) {
            $date2_int = strtotime(date('Y-m-d', time()));
        } else {
            $date2_int = strtotime($date2);
        }



        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);

        $sql = 'select * from `' . sh . '_counts`as c where 1 ';
        $sql .= ' and mytype="goodshit" ';
        /* 搜店铺 */
        // print_r($comid);die;
        if ('' != $comid) {
            $sql .= ' and c.comid=:comid';
            $para[':comid'] = $comid;
            $result = $this->pdo->fetchOne($sql, $para);
            if (false == $result) {
                $this->ckerr('没找到这个店铺');
                return false;
            }
        }

        /* date */
        $sql .= ' and c.stimeint>=:date1_int';
        $sql .= ' and c.stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);

        $sql .= ' order by id desc ';
        if ($this->act == '') {
            $result = $this->main->exers($sql, $para);
        } else {
            $result = $this->pdo->fetchAll($sql, $para, true);
            //$result['rs']=$result;
        }



        $this->j['list'] = $result;

//        print_r($result);die;
    }

//    function doexport() {
//        $sql = 'select * from `' . sh . '_counts` where 1 ';
//        $sql .= ' and mytype="goodshit" ';
//        $sql .= ' order by id asc ';
//
//        $result = $this->pdo->fetchAll($sql);
//
//        $this->j['list'] = $result;
//    }
}

$myapi = new myapi();
unset($sys_admin_user);
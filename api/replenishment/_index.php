<?php

/* 补货首页 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'replenishment/_main.php'; //补货页通用数据

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
                $this->pagemain();
                $this->output();
                break;
        }
    }

    /**/

    function pagemain() {
        $sql = 'select device.* ';
        $sql .= ' ,place.building, place.floor, place.title, place.id as placeid ';
        $sql .= ' from `' . sh . '_device` as device ';
        $sql .= ' left join `' . sh . '_place` as place on device.placeid=place.id ';
        $sql .= ' where 1 ';
        $sql .= ' and device.placeid>0 ';
        $sql .= ' and device.comid=:comid ';
        $sql .= ' order by cls asc, device.placeid asc ';

        $result = $this->pdo->fetchAll($sql, Array(':comid' => $this->j['user']['comid']));

        $GLOBALS['j']['list'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

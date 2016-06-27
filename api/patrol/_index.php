<?php

/* 查货首页 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'patrol/_main.php'; //补货页通用数据
//require_once '_power.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        
        $this->j = & $GLOBALS['j'];
        $this->modulemain = new cls_modulemain();/* 检测权限类 */
        
        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
        
        /* ============================== */
        /* 什么情况下必须返回json格式 */

        $jsonact = array('json'
            , 'esave'
            , 'nsave'
            , 'loginin'
            , 'loginout'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }



        switch ($this->main->ract()) {
            case '':
                $this->main();
                $this->output();
                break;
        }
    }

    /**/

    function main() {

        $sql = 'select device.* ';
        $sql .= ' ,place.building, place.floor, place.title as placetitle, place.id as placeid ';
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

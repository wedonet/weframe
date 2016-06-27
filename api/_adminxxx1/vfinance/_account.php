<?php

/* 平台储值卡统计接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . '_adminxxx1/_main.php'; //检测权限


/* 返回 */

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

    function pagemain() {
//        $sql = 'select * from `' . sh . '_vmoneyuser` where 1 ';
        $sql = 'select sum(myvalue) as myvalue , sum(myvalueout) as myvalueout from`' . sh . '_vmoneyuser` where 1 ';//用vmoneyuser表的收入和支出累加=管理员后台的总发放和总消费
        $result = $this->pdo->fetchOne($sql);

        $this->j['data'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

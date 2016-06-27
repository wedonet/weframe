<?php

/* 平台财务接口 */
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
                $this->main();
                $this->output();
                break;
        }
    }

    function main() {
        $sql = 'select * from `' . sh . '_account` where mytype="plat" limit 1 ';

        $result = $this->pdo->fetchOne($sql);

        $this->j['data'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

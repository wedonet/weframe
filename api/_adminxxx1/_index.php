<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';

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

        switch ($this->act) {

            default:
                $this->pagemain();
                break;
        }
    }

    function pagemain() {
        
    }

}

$myapi = new myapi();
unset($myapi);

<?php

require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'biz/_main.php'; //公共

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->modulemain = new cls_modulemain(); //权限

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }



        /* 什么情况下必须返回json格式 */
        $jsonact = array();
        if (array_key_exists($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        };

     

        switch ($this->act) {
            case '':
                $this->thismain();
                $this->output();
                break;
            default:
                break;
        }
    }

    function thismain() {
        $sql = 'select * from `' . sh . '_account` where 1 ';

        $sql .= ' and mytype="biz" ';

        $sql .= ' and comid=:comid';

        $para[':comid'] = $this->main->user['comid'];

        $result = $this->pdo->fetchOne($sql, $para);

        $this->j['data'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

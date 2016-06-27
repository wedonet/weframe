<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once syspath . '_inc/cls_form.php';

require_once AdminApiPath . '_main.php'; //本模块数据

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
            case '':
                $this->pagemain();
                $this->output();
                break;
            
        }
    }

    function pagemain() {
//               $suid = $this->main->rqid('id');
        $c_form = new cls_form();
       
       
        /* 提取数据 */
        $sql = 'select * from ' . sh . '_joinusform';
        $sql .= ' order by id desc ';
        $result = $this->main->exers($sql);

        $this->j['list'] = $result;
    }
    


}

$myapi = new myapi();
unset($myapi);

<?php

/* 补货首页 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'form/_main.php'; 

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
        /*提取调查*/
        $sql = 'select * from `'.sh.'_form` where 1 ';
      //  $sql .= ' and mystatus="doing" ';  所有状态的问卷均显示
        $sql .= ' order by mycount desc ';
        
        $this->j['list'] = $this->pdo->fetchAll($sql);
    }

}

$myapi = new myapi();
unset($myapi);

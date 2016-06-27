<?php

/* 购物车 */


require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'member/_main.php'; //用户后台通用数据


/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->j = & $GLOBALS['j'];
        $this->modulemain = new cls_modulemain();
        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
        $this->act = $this->main->ract();

        switch ($this->act) {
            case '':
                $this->pagemain();
                break;
        }
    }

    function pagemain() {
       $userid = $this->main->user['id'];
        $sql = 'select * ';
        $sql .= ' from   ' . sh . '_vmoneyuser  ';      
        $sql .= '  where uid=:uid';
//        print_r($userid);die;
        $sql .= ' order  by  id  desc';
        
        $resultvmoneyuser = $this->pdo->fetchAll($sql, Array(':uid' => $userid));
        $GLOBALS['j']['list'] = $resultvmoneyuser;
     
     
    }

}

$myclassapi = new myapi();

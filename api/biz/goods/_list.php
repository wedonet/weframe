<?php

/* 补货首页 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'biz/_main.php';

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
        $sql = 'select comgoods.*, goods.title,goods.preimg as preimg from `' . sh . '_comgoods` as comgoods  ';
        $sql .= ' left join `' . sh . '_goods` as goods  '; //join 查询 去取名称等信息
        $sql .= ' on comgoods.goodsid=goods.id ';
        $sql .= ' where 1 ';
        $sql .= ' and comgoods.comid=:comid ';
        $sql .= ' and comgoods.comid=:comid';
        
        $para['comid'] = $this->main->user['comid'];

        $this->j['list'] = $this->pdo->fetchAll($sql, $para);
    }

}

$myapi = new myapi();
unset($myapi);

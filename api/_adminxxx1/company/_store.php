<?php

/* 店铺出入库记录 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once AdminApiPath . '_main.php';
require_once '_main.php'; /* 业务管理通用数据 */

/* */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        $this->comid = $this->main->rid('comid');

        $c_company = new cls_companymain();

        /* 店铺信息添加进$globals['j']['company'] */
        $this->j['company'] = $c_company->getcompany($this->comid);

        

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;         
        }
    }

    function mylist() {
        $sql = 'select s.* ';
        $sql .= ' ,g.preimg as preimg ';
        $sql .= ' ,g.title as title ';
        $sql .= ' from `' . sh . '_comstore` as s ';
        $sql .= ' left join `'.sh.'_goods` as g on s.goodsid=g.id ';
        $sql .= ' where 1 '; 
        $sql .= ' and s.comid=' . $this->comid;
        $sql .= ' order by id desc ';

        $result = $this->main->exers($sql);

        $this->j['list'] = $result;
    }

   

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
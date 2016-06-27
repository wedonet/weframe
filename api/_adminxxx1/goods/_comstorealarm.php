<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once AdminApiPath . '_main.php';

/* 店铺库存警报 */

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

    /* 用户列表 */

    function pagemain() {
        $comtitle=$this->main->request("comtitle","店铺名称",0,255,'char');
         $goodstitle=$this->main->request("goodstitle","商品名称",0,255,'char');
        //print_r($comtitle);
          // print_r($goodstitle);die;
         //传回前端
           $this->j['search']['comtitle'] = $comtitle;
           $this->j['search']['goodstitle'] = $goodstitle;
        /* 提取低于警戒值的商品 */
        $sql = 'select comgoods.* ';
        $sql .= ' ,goods.title,goods.comid as comid,goods.preimg as preimg ';
        $sql .= ' ,com.title as comname ';
        $sql .= ' from `' . sh . '_comgoods` as comgoods  ';
        $sql .= ' left join `' . sh . '_goods` as goods  '; //join 查询 去取名称等信息
        $sql .= ' on comgoods.goodsid=goods.id ';
        $sql .= ' left join `'.sh.'_com` as com';
        $sql .= ' on comgoods.comid=com.id';
        $sql .= ' where 1 ';
     
        if( ''!=$comtitle)
        {
              $sql.= ' and com.title like "%'.$comtitle.'%" '; 
        }
         if( ''!=$goodstitle)
        {
              $sql .= ' and goods.title like "%'.$goodstitle.'%" '; 
        }
        $sql .= ' and comgoods.inventories<comgoods.inventoriesalarm ';
        $sql .= ' order by comgoods.comid ';

       // print_r($sql);die;

        $result = $this->main->exers($sql);

    

        $this->j['list'] = $result;
    }

   

}

$myapi = new myapi();
unset($myapi);

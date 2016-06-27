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
        $this->posttype = 'post';
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

        if (!$this->ckerr()) {
            return false;
        }

        /* 变成整形，如果没填日期默认30天内的 */
        if ('' == $date1) {
            $date1_int = strtotime(date('Y-m-d', (time() - 30 * 24 * 3600)));
        } else {
            $date1_int = strtotime($date1);
        }

        if ('' == $date2) {
            $date2_int = strtotime(date('Y-m-d', time()));
        } else {
            $date2_int = strtotime($date2);
        }

        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }

        /* 传回前端 */
        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);
        

        /* 提取所有店铺商品 */
        $sql = 'select comgoods.*,goods.title as title,goods.preimg as preimg from `' . sh . '_comgoods` as comgoods ';
        $sql .= ' left join `' . sh . '_goods` as goods on comgoods.goodsid=goods.id ';
        $sql .= ' where 1 ';
        $sql .= ' and comgoods.comid=:comid';
        
        unset($para);
        $para[':comid'] = $this->main->user['comid'];

        $result = $this->pdo->fetchAll($sql, $para);

        $goodslist = false;
        /* 循环商品，把comgoodsid当索引 */
        foreach ($result as $v) {
            $goodslist[$v['id']] = $v;
            $goodslist[$v['id']]['mycount'] = 0;
        }


        /*提取定单*/
        $sql = 'select comgoodsids from `' . sh . '_order` where 1 ';
        $sql .= 'and ispayed=1';
        $sql .= ' and comid=:comid';

        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        
        $para= null;
        $para[':comid'] = $this->main->user['comid'];
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);

        $result = $this->pdo->fetchAll($sql, $para);

        if (false != $goodslist) {
            foreach ($result as $v) {
                $comgoodsids = $v['comgoodsids'];
                $a = explode(',', $comgoodsids);

                foreach ($a as $z) {
                    $goodslist[$z]['mycount'] ++;
                }
            }
        }


        $this->j['list'] = $goodslist;
    }

}

$myapi = new myapi();
unset($myapi);

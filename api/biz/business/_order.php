<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_biz.php';
require_once ApiPath . 'biz/_main.php';


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

        $this->act = $this->main->ract();

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;
            case 'export':
                $this->mylist();
                break;
        }
    }

    function mylist() {
        $c_biz = new cls_biz();
        $this->j['search']['mystatus'] = $c_biz->orderstatus;
        $this->posttype = 'get';
        $mystatus = $this->main->request('mystatus', '订单状态', 1, 50, 'char', '', false);
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

        /* 传回前端 */
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;

        if (!$this->ckerr()) {
            return false;
        }
        /* check date */
        if ('' == $date1) {
            $date1_int = strtotime(date('Y-m-d', (time() - 7 * 24 * 3600)));
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

        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);

        $sql = 'select o.*,p.building,p.floor,p.title as placetitle from `' . sh . '_order` as o';

        $sql .= ' left join `' . sh . '_place` as p on o.placeid=p.id ';

        
        $sql .= ' where o.comid=:comid';
        $sql .= ' and o.order_type=0';
        /* date */
        $sql .= ' and o.stimeint>=:date1_int';
        $sql .= ' and o.stimeint<=:date2_int';
        if (!empty($mystatus)) {
            $sql .= ' and o.mystatus=:mystatus';
            $para[':mystatus'] = $mystatus;
        }
        $sql .= ' order by o.id desc ';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);
        

        $para[':comid'] = $this->main->user['comid'];
        if ('export' == $this->act) {
            $result = $this->pdo->fetchAll($sql, $para);
            $i = 0;

            foreach ($result as $v) {
                $result[$i]['mystatusname'] = $c_biz->orderstatus[$v['mystatus']];
                $i++;


                $this->j['list'] = $result;
            }
        } else {


            $result = $this->main->exers($sql, $para);
            //   print_r($result);die;
            $this->j['list'] = $result;
            //print_r($result);die;
            //$a_orderid = Array();
            if ('' == $this->act) {
                $i = 0;

                foreach ($result['rs'] as $v) {
                    $result['rs'][$i]['mystatusname'] = $c_biz->orderstatus[$v['mystatus']];
                    $i++;
                }

                $this->j['list'] = $result;
            }
        }
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
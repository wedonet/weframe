<?php

/* 定单管理 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
require_once syspath . '_inc/cls_biz.php';
require_once ApiPath . '_adminxxx1/_main.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        $this->act = $this->main->ract();

        switch ($this->act) {
            case '':
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
        $this->j['search']['mystatus'] =$c_biz->orderstatus;
        $c_money = new cls_money();
        $myway = $c_money->myway;


        $this->posttype = 'post';
        $comic = $this->main->request('comic', '店铺编码', 1, 50, 'char', '', false);
        $mystatus = $this->main->request('mystatus', '店铺编码', 1, 50, 'char', '', false);
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

        /* 传回前端 */
        $this->j['search']['comic'] = $comic;
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['comname'] = '';

        if (!$this->ckerr()) {
            return false;
        }


        /* check com */
        if ('' != $comic) {
            $sql = 'select id,title from `' . sh . '_com` where ic=:comic';
            $result = $this->pdo->fetchOne($sql, Array(':comic' => $comic));
            if (false == $result) {
                $this->ckerr('没找到这个店铺');
                return false;
            } else {
                $a_com = $result;
                $this->j['search']['comname'] = $result['title'];
            }
        } else {
            $this->j['search']['comname'] = '';
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



        $sql = 'select o.*,p.building,p.floor,p.title as doornum,com.ic as comic from `' . sh . '_order` as o ';
        $sql .= ' left join `' . sh . '_place` as p on o.placeid=p.id ';
        $sql .= ' left join `' . sh . '_com` as com on o.comid=com.id ';
        $sql .= ' where 1 ';
        $sql .= ' and o.order_type=0 ';
        if ('' != $comic) {
            $sql .= ' and o.comid=:comid';
            $para[':comid'] = $a_com['id'];
        }
        
        if(!empty($mystatus)){
            $sql .= ' and o.mystatus=:mystatus';
            $para[':mystatus'] = $mystatus;
        }

        /* date */
        $sql .= ' and o.stimeint>=:date1_int';
        $sql .= ' and o.stimeint<=:date2_int';
        $sql .= ' order by o.id desc';

        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);


//		if('' == $this->act){
//			$this->j['list'] = $this->main->exers($sql, $para);
//		}else{
//			$this->j['list'] = $this->pdo->fetchAll($sql, $para);
//		}

        if ('export' == $this->act) {
            $result = $this->pdo->fetchAll($sql, $para);
            $i = 0;

            foreach ($result as $v) {
                if (array_key_exists($v['payway'], $myway)) {
                    $result[$i]['mywayname'] = $myway[$v['payway']]['title'];
                } else {
                    $result[$i]['mywayname'] = '';
                }

                if (array_key_exists($v['mystatus'], $c_biz->orderstatus)) {
                    $result[$i]['mystatusname'] = $c_biz->orderstatus[$v['mystatus']];
                } else {
                    $result[$i]['mystatusname'] = $v['mystatus'];
                }

                $i++;
            }

            $this->j['list'] = $result;
            return;
        }

        $result = $this->main->exers($sql, $para);

        if ('' == $this->act) {
//            $result['rs'] = $this->pdo->fetchAll($sql, $para);
            $i = 0;

            foreach ($result['rs'] as $v) {
                $result['rs'][$i]['mywayname'] = '';

                if ('' != ($v['payway'] . '')) {
                    $result['rs'][$i]['mywayname'] = $myway[$v['payway']]['title'];
                }
                $i++;
            }

            $this->j['list'] = $result;



            if ('' == $this->act) {
                $i = 0;

                foreach ($result['rs'] as $v) {
                    $result['rs'][$i]['mystatusname'] = $c_biz->orderstatus[$v['mystatus']];


                    $i++;
                }

                $this->j['list'] = $result;


                return;
            }
        }
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
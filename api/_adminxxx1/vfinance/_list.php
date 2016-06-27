<?php

/* 平台储值卡接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
require_once ApiPath . '_adminxxx1/_main.php'; //检测权限

/* 返回 */

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
        $c_money = new cls_money();
        $moneysetting = $c_money->moneysetting;


        $this->posttype = 'post';
        
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

        /* 传回前端 */
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;

        if (!$this->ckerr()) {
            return false;
        }

        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
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



        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);



//        $sql = 'select money.*from `' . sh . '_vmoneyuser` as money ';
//        $sql .= ' where 1 ';
        $sql = 'select money.*, user.u_nick as unick from `' . sh . '_vmoneyuser` as money ';
        $sql .= ' left join `' . sh . '_user` as user on money.uid=user.id ';
        $sql .= ' where 1 ';


        /* date */
        $sql .= ' and money.stimeint>=:date1_int';
        $sql .= ' and money.stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);


        $sql .= ' order by money.id desc ';



        $result = $this->main->exers($sql, $para);


        $this->j['list'] = $result;





        /* 统计 */
        $sql = 'select sum(myvalue) as myvalue,sum(myvalueout) as myvalueout, count(*) as mycount from `' . sh . '_vmoneyuser` where 1 ';

        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);

        $result = $this->pdo->fetchOne($sql, $para);
        $this->j['account'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

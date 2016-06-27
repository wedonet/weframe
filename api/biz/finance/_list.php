<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';

require_once ApiPath . 'biz/_main.php'; //权限

/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->modulemain = new cls_modulemain(); //权限

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

//print_r($this->act);die;
        switch ($this->act) {
            case '':
                $this->mylist();
                break;
            case 'export':
//                 print_r('export');die;
                $this->mylist();
                $this->output();
                break;
            default:
                break;
        }
    }

    function mylist() {
        $c_money = new cls_money();
        $moneysetting = $c_money->moneysetting;


        $this->posttype = 'get';

        $dates = $this->main->getdates(strtotime(date('Y-m-d', (time() - 7 * 24 * 3600))), strtotime(date('Y-m-d', time())));
        $myfrom = $this->main->request('myfromname', '订单类型', 0, 255, 'char', '', false);

        /* 传回前端 */
        $this->j['search']['date1'] = $dates['date1'];
        $this->j['search']['date2'] = $dates['date2'];
        $this->j['search']['myfrom'] = $myfrom;
        $this->j['account'] = 0;
        if (!$this->ckerr()) {
            return false;
        }


        /* 提取商家财务记录 */
        $sql = 'select money.*,o.mygoods as mygoods,case when money.myfrom="shendeng" then "神灯订单" when money.myfrom="diannei" then "店内有售" end as myfromname from `' . sh . '_moneycom` as money ';
        $sql .= ' left join `' . sh . '_order` as o on money.orderid=o.id ';
        $sql .= ' where 1 ';
        /* date */
        $sql .= ' and money.stimeint>=:date1_int';
        $sql .= ' and money.stimeint<=:date2_int';
        $para[':date1_int'] = $dates['int1'];
        $para[':date2_int'] = $dates['int2'] + (24 * 3600);

        /* 加上所属店铺 */
        $sql .= ' and money.comid=:comid';
        $para[':comid'] = $this->main->user['comid'];
        if ('' !== $myfrom) {
            $sql .= ' and money.myfrom=:myfrom'; //神灯的
            $para[':myfrom'] = $myfrom;
        }

        $sql .= ' order by money.id desc ';
//        print_r($sql);
//        print_r($para);die;
        if ('export' == $this->act) {
            $result = $this->pdo->fetchAll($sql, $para);
            $i = 0;
            foreach ($result as $v) {
                $result[$i]['mytypename'] = $moneysetting[$v['mytype'] * 1];
                $i++;
            }
            $this->j['list'] = $result;
            return;
        }

        $result = $this->main->exers($sql, $para);
        /* 格式化款项类型 */
        $i = 0;
        foreach ($result['rs'] as $v) {
            $result['rs'][$i]['mytypename'] = array_key_exists($v['mytype'], $moneysetting) ? $moneysetting[$v['mytype'] * 1] : '未知款项:' . $v['mytype'];
            $i++;
        }

        $this->j['list'] = $result;




        unset($para);
        /* 统计 */
        $sql = 'select sum(myvalue) as myvalue,sum(myvalueout) as myvalueout, count(*) as mycount from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and comid=:comid';
        $para[':comid'] = $this->main->user['comid'];


        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $para[':date1_int'] = $dates['int1'];
        $para[':date2_int'] = $dates['int2'] + (24 * 3600);
 if ('' !== $myfrom) {
          $sql .= ' and myfrom=:myfrom'; //神灯的
          $para[':myfrom']=$myfrom;
        }
        $result = $this->pdo->fetchOne($sql, $para);
        $this->j['account'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

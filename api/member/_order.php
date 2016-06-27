<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_biz.php';

require_once ApiPath . 'member/_main.php'; //用户后台通用数据

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
                break;
            default:
                break;
        }
    }

    function pagemain() {
        $c_biz = new cls_biz();

        /* 提取定单 */
        $sql = 'select o.*,c.title as comname from `' . sh . '_order` as o ';
        $sql .= ' left join `' . sh . '_com` as c on o.comid=c.id ';
        $sql .= ' where 1 ';
        $sql .= ' and o.uid=:uid';
        //$sql .= ' and o.mytype >0 and o.ispayed>0'; //不显示未支付的临时定单

        $sql .= ' and o.id not in(select o.id from `' . sh . '_order` where o.ispayed=0) ';
        $sql .= ' order by o.id desc ';
        $para[':uid'] = $this->main->user['id'];

        $result = $this->main->exers($sql, $para);

        $this->j['list'] = $result;

        $a_orderid = Array();

        $i = 0;
        foreach ($result['rs'] as $v) {
            /* 处理定单状态 */
            if (array_key_exists($v['mystatus'], $c_biz->orderstatus)) {
                $this->j['list']['rs'][$i]['mystatusname'] = $c_biz->orderstatus[$v['mystatus']];
            } else {
                $this->j['list']['rs'][$i]['mystatusname'];
            }
            $i++;
        }







        /* 返回所有商品列表 */
//            unset($a);
//        $a[1]['id'] = 1; //流水号
//        $a[1]['title'] = '矿泉水'; //流水号
//        $a[1]['price'] = 1000; //流水号
//        $a[1]['preimg'] = '/_images/photos/drink1/pic.jpg';
//
//
//        $a[2]['id'] = 1; //流水号
//        $a[2]['title'] = '矿泉水'; //流水号
//        $a[2]['price'] = 1000; //流水号
//        $a[2]['preimg'] = '/_images/photos/drink1/pic.jpg';
//
//        $a[3]['id'] = 1; //流水号
//        $a[3]['title'] = '矿泉水'; //流水号
//        $a[3]['price'] = 1000; //流水号    
//        $a[3]['preimg'] = '/_images/photos/drink1/pic.jpg';
        //$this->j['listgoods'] = $a;
    }

}

$myapi = new myapi();
unset($myapi);

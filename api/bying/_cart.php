<?php

/* 扫购 - 购物车 */

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_style/cls_template.php';
require_once syspath . '_inc/cls_api.php';

class myclassapi extends cls_api {

    function __construct() {
        parent::__construct();

        switch ($this->act) {

            case '':
                $this->pagemain();
                break;
            case 'add':
                $_POST['outtype'] = 'json';
                //$dir = $_SERVER['SCRIPT_FILENAME'] . '_' . time() . '.htm';
                /* $GLOBALS['we']->write_file($dir , pr()); */

//                function domoney($money) {
//                    $money['title'] = '';
//                    $money['mywayic'] = '';
//                    $money['amoun'] = '';
//                    $money['formcode'] = '';
//                    $money['formdate'] = '';
//                    $money['uid'] = '用户id';
//                    $money['orderid'] = '定单id';
//                    $money['comid'] = '店铺id';
//                    $money['other'] = '备注';
//
//                    //$money['duid']
//                    //money['dnick']
//                    //下面这几个跟据入同账本有变化
//                    $money['action'] = 'add, substract';
//                    $money['mytype'] = '款项编码';
//                    $money['acceptgroup'] = '收款的用户类型 user com plat';
//                }

                $this->addtocart();
                $this->output();
                break;
        }
    }

    function pagemain() {
        /* 商品详情 */
        /* $detail['gic'] = '1'; //商品ic
          $detail['dic'] = '1'; //柜门ic
          $detail['title'] = '模拟商品名称'; //商品名称
          $detail['price'] = '2.5'; //价格
          $detail['preimg'] = '/_images/photos/pic_7.jpg'; //图片路径
          $detail['content'] = '商品描述商品描述商品描述商品描述商品品描描述品描述商品描述';//商品描述
          $detail['readme'] = '商品描述简介品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述';//商品简介
          $this->j['detail']= $detail; */
        //stop(unserialize($_COOKIE[CacheName.'proinfo']),true);
        //stop($_COOKIE[CacheName.'_cart']);
        //$savetime = time() + 3600;
        $savetime = 0;

        $list = array();
        $cart['allprice'] = 0;
        $cart['counts'] = 0;
        $dooridfst = 0;
        if (isset($_COOKIE[CacheName . '_cart']) && '' !== $_COOKIE[CacheName . '_cart']) {
            //stop($_COOKIE[CacheName.'_cart']);
            $doorarr = explode(',', $_COOKIE[CacheName . '_cart']);
            $dooridfst = end($doorarr);
            $sql = 'select d.id as doorid,d.mystatus,d.doorstatus,d.comid,d.ic as dic,d.hasgoods as hasgood,cg.id as comgoodsids,g.id as gids,g.title,g.ic as gic,g.preimg,cg.price,cg.commission,d.placeid,d.deviceid from `' . sh . '_door` as d,`' . sh . '_comgoods` as cg,`' . sh . '_goods` as g';
            $sql .= ' where d.goodsid=g.id and g.id=cg.goodsid and d.id in (' . $_COOKIE[CacheName . '_cart'] . ')';
            $sql .= ' and d.comid=cg.comid';
            //$sql .= ' group by g.id';
            $list = $this->pdo->fetchAll($sql);

            //stop($list,true);
            foreach ($list as $val) {
                $cart['allprice'] += $val['price']; //订单总额
            }
            $num = count($list);
            $cart['counts'] = $num; //商品数量
            $a = json_encode($list);

            setcookie(CacheName . 'proinfo', $a, $savetime);
            //setcookie(CacheName.'proinfo',serialize($list),$savetime);
            //返回设备状态
            //stop(json_decode($_COOKIE[CacheName.'proinfo'],true),true);
            /* if (isset($_COOKIE['mypro'])) {
              $cc = stripslashes($_COOKIE['mypro']);
              stop(unserialize($_COOKIE['mypro']),true);
              } */
            $this->j['list'] = $list;
            $GLOBALS['j']['cart'] = $cart;
            $this->j['dooridfst'] = $dooridfst;
            unset($sql);
            $sql = 'select device.mystatus  ';
            $sql .= ' from `' . sh . '_door` as door ';
            $sql .= ' inner join  `' . sh . '_device` as device  ';
            $sql .= ' on device.id=door.deviceid ';
            $sql .= ' where door.id in (' . $_COOKIE[CacheName . '_cart'] . ')';
            $a_door = $this->pdo->fetchAll($sql);

            
            //取cookie的酒店信息
        if (isset($_COOKIE[CacheName . '_comid'])) {

            $comid = $_COOKIE[CacheName . '_comid'];

            $this->main->getcominfo($comid);
            $this->j['company'] = $this->main->company;
            //print_r($comid);die;     
        }
        
        
        
            foreach ($a_door as $v) {
                if ($v['mystatus'] != "doing") {
                    $this->j['devicemystatus'] = 'n';
                    return;
                }
                $this->j['devicemystatus'] = 'y';
            }
        } else {
            $this->j['devicemystatus'] = 'y';

            $this->j['list'] = $list;

            $GLOBALS['j']['cart'] = $cart;
            $this->j['dooridfst'] = $dooridfst;
        }
        /* if(1==$this->get('isprint')){
          print_r($this->j);} */


    }

    function addtocart() {
        //stop($_COOKIE[CacheName.'_cart']);
        $doorid = $this->main->rqid('doorid');
        //$savetime = time() + 3600;
        $savetime = 0;
        $cartarr = array();
        /* 检测这个门里有没有商品 */
        $sql = 'select * from `' . sh . '_door` where 1 ';
        $sql .= ' and id=:doorid';
        $a_door = $this->pdo->fetchOne($sql, Array(':doorid' => $doorid));

        if (false == $a_door) {
            $this->j['success'] = 'n';
            return;
        }
        if (0 == $a_door['hasgoods']) {
            $this->j['success'] = 'n';
            return;
        }

        if (isset($_COOKIE[CacheName . '_cart'])) {
            $proid = explode(',', $_COOKIE[CacheName . '_cart']);
            foreach ($proid as $val) {
                if ($val == $doorid) {
                    $this->j['success'] = 'n';
                    return;
                }
            }
            $newlist = $_COOKIE[CacheName . '_cart'] . ',' . $doorid;
            setcookie(CacheName . '_cart', $newlist, $savetime);
        } else {
            setcookie(CacheName . '_cart', $doorid, $savetime);
        }
        //stop($_COOKIE[CacheName.'_cart']);
        $this->j['success'] = 'y';
    }

}

$myclassapi = new myclassapi();

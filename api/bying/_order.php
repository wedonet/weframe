<?php

/* 生成定单 */

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_biz.php';

require_once ApiPath . 'bying/_main.php'; //扫购通用数据

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->c_bying = new cls_bying();


        switch ($this->act) {
            case 'batch': //由购物车生成定单
                $this->batchcart(); //提交
                $this->output();
                break;
            case 'getprice': //由购物车取数量和总价             
                $this->getprice(); //提交
                $this->output();
                break;
            default:
                $this->main();
                $this->output();
                break;
        }
    }

    /* 接收doorid,生成临时定单，返回定单号，跳转到支付页 */

    function main() {
        $doorid = $this->main->rqid('doorid');

        /* 检测这个门里有没有商品 */
        $sql = 'select * from `' . sh . '_door` where 1 ';
        $sql .= ' and id=:doorid';
        $a_door = $this->pdo->fetchOne($sql, Array(':doorid' => $doorid));


        if (false == $a_door) {
            $this->returnerr('没找到这个门啊！');
            return;
        }
        if (0 == $a_door['hasgoods']) {
            $this->returnerr('这个柜门里没有商品，请联系酒店补货或继续选购其它商品！');
            return;
        }

        /* 提取商品 */
        $sql = 'select * from `' . sh . '_goods` where 1 ';
        $sql .= ' and id=:goodsid';
        $a_goods = $this->pdo->fetchOne($sql, Array(':goodsid' => $a_door['goodsid']));
        if (false == $a_goods) {
            $this->returnerr('没找到这件商品');
            return;
        }



        /* 提店铺商品 */
        $sql = 'select * from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and id=:comgoodsid';
        $a_comgoods = $this->pdo->fetchOne($sql, Array(':comgoodsid' => $a_door['comgoodsid']));


        /* ==============================
         * 事务处理
         */
        $pdo = & $GLOBALS['pdo'];
        try {
            $pdo->begintrans();

            /* 保存定单 */
            $rs['allprice'] = $a_comgoods['price'];
            $rs['commission'] = $a_comgoods['commission'];
            $rs['stimeint'] = time();
            $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);
            $rs['uid'] = $this->main->user['id'];
            $rs['ispayed'] = 0;
            $rs['mystatus'] = 'new';
            $rs['mytype'] = 0;
            $rs['gids'] = $a_comgoods['goodsid'];
            $rs['placeid'] = $a_door['placeid'];
            $rs['doorids'] = $doorid;
            $rs['deviceid'] = $a_door['deviceid'];
            $rs['comgoodsids'] = $a_comgoods['id'];
            $rs['comid'] = $a_door['comid'];

            $rs['doorstatus'] = '';
            $rs['alllocker'] = $a_door['title'];

            $uid = $rs['uid'];
            $orderid = $this->pdo->insert(sh . '_order', $rs);


            /* 存定单商品 */
            unset($rs);
            $rs['orderid'] = $orderid;
            $rs['title'] = $a_goods['title'];
            $rs['price'] = $a_comgoods['price'];
            $rs['allprice'] = $a_comgoods['price'];
            $rs['goodsid'] = $a_goods['id'];
            $rs['comgoodsid'] = $a_comgoods['id'];
            $rs['comid'] = $a_door['comid'];
            $rs['preimg'] = $a_goods['preimg'];
            $rs['placeid'] = $a_door['placeid'];
            $rs['deviceid'] = $a_door['deviceid'];
            $rs['doorid'] = $doorid;
            $rs['doortitle'] = $a_door['title'];
            $rs['counts'] = 1;

            $insertid = $this->pdo->insert(sh . '_ordergoods', $rs);

            /* 预处理定单商品,存进定单表mygoods字段 */
            $c_biz = new cls_biz();
            $c_biz->updateordergoods($orderid);



            $pdo->submittrans();
            //将订单号保存在session中，以便登陆后更新此订单uid（现在uid为0）
            if ($orderid != 0 && $uid == 0) {

                $cach = $GLOBALS['config']['CacheName'] . 'orders';
                if (!isset($_SESSION[$cach])) {
                    $_SESSION[$cach] = $orderid;
                    //$orders = $_SESSION[$cach];
                } else {
                    $orders = $_SESSION[$cach];
                    $orders .= ',' . $orderid;
                    $_SESSION[$cach] = $orders;
                }
            }
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }



        /* 存定单商品 */


        $this->j['orderid'] = $orderid;
        $this->j['success'] = 'y';
    }

    function batchcart() {
        /* 接收传递过来的柜门列表 */
        $dooridlist = $this->main->ridlist('doorid');

        if ('' == $dooridlist) {
            $this->ckerr('购物车内还没有商品');
            return;
        }

        /* 提取所有柜门的信息，联查店铺商品表 */
        $sql = 'select d.*, ';
        $sql .= ' c.price as price, c.commission as commission ';
        $sql .= ' ,g.title as goodstitle, g.preimg as preimg ';
        $sql .= ' from `' . sh . '_door` as d ';
        $sql .= ' left join `' . sh . '_comgoods` as c on d.comgoodsid=c.id ';
        $sql .= ' left join `' . sh . '_goods` as g on d.goodsid=g.id ';
        $sql .= ' where 1 ';
        $sql .= ' and d.id in (' . $dooridlist . ')';

        $a_door = $this->pdo->fetchAll($sql);

        if (false == $a_door) {
            $this->ckerr('没找到对应柜门');
            return;
        }

        /* 取定单对应门号 */
        $alllocker = join(',', array_column($a_door, 'title'));

        /* 计算价格佣金，商品列表，店铺商品列表 */
        $allprice = 0;
        $commission = 0;
        $gids = array();
        $comgoodsids = array();
        foreach ($a_door as $v) {
            $allprice += $v['price'];
            $commission += $v['commission'];
            $gids[] = $v['goodsid'];
            $comgoodsid[] = $v['comgoodsid'];
        }

        /* ==============================
         * 事务处理
         */
        $pdo = &$GLOBALS['pdo'];
        try {
            $pdo->begintrans();

            /* 生成定单 */
            $currenttime = time();


            /* 添加定单 */
            $rs['stime'] = date('Y-m-d H:i:s', $currenttime);
            $rs['stimeint'] = $currenttime;

            $rs['uid'] = $this->main->user['id'];
            $rs['ispayed'] = 0;
            $rs['mystatus'] = 'new';
            $rs['comid'] = $a_door[0]['comid'];

            $rs['mytype'] = 1; //购物车为正式订单
            $rs['doorids'] = $dooridlist;

            $rs['placeid'] = $a_door[0]['placeid'];
            $rs['deviceid'] = $a_door[0]['deviceid'];

            $rs['allprice'] = $allprice;
            $rs['commission'] = $commission;
            $rs['gids'] = join(',', $gids);
            $rs['comgoodsids'] = join(',', $comgoodsid);

            $rs['doorstatus'] = '';
            $rs['alllocker'] = $alllocker;

            $uid = $rs['uid'];
            $orderid = $this->pdo->insert(sh . '_order', $rs);
            // print_r($orderid);
            // print_r($uid);
            // die;
            /* 添加定单商品 */
            /*      把商品存进定单商品列表 */
            unset($rs);
            foreach ($a_door as $v) {
                $rs['orderid'] = $orderid;
                $rs['title'] = $v['goodstitle'];
                $rs['price'] = $v['price'];
                $rs['allprice'] = $v['price'];
                $rs['goodsid'] = $v['goodsid'];
                $rs['comgoodsid'] = $v['comgoodsid'];
                $rs['comid'] = $v['comid'];
                $rs['preimg'] = $v['preimg'];
                $rs['placeid'] = $v['placeid'];
                $rs['deviceid'] = $v['deviceid'];
                $rs['doorid'] = $v['id'];
                $rs['doortitle'] = $v['title'];
                $rs['counts'] = 1;

                $this->pdo->insert(sh . '_ordergoods', $rs);
            }

            /* 预处理定单商品,存进定单表mygoods字段 */
            $c_biz = new cls_biz();
            $c_biz->updateordergoods($orderid);

            $pdo->submittrans();
            //将订单号保存在session中，以便登陆后更新此订单uid（现在uid为0）
            if ($orderid != 0 && $uid == 0) {

                $cach = $GLOBALS['config']['CacheName'] . 'orders';
                if (!isset($_SESSION[$cach])) {
                    $_SESSION[$cach] = $orderid;
                    //$orders = $_SESSION[$cach];
                } else {
                    $orders = $_SESSION[$cach];
                    $orders .= ',' . $orderid;
                    $_SESSION[$cach] = $orders;
                }
            }
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        /* 清空购物车 */
        if (isset($_COOKIE[CacheName . 'probuy'])) {
            setcookie(CacheName . 'probuy', '', -999999);
        }

        $this->j['success'] = 'y';
        $this->j['orderid'] = $orderid;
    }

    function getprice() {
        $allprice = 0;
        $doors = $this->main->request('dooridlist', '柜门列表', 0, 99999, 'char');
        //stop($doors);
        $doorarr = explode(',', $doors);

        $sql = 'select comgoodsid from `' . sh . '_door` where 1=1 and id in (' . $doors . ')';
        $res = $this->pdo->fetchAll($sql);
        $rs = array_column($res, 'comgoodsid');
        $group = array(); //每个comgoodsid对应的个数
        foreach ($rs as $val) {
            if (!isset($group[$val])) {
                $group[$val] = 1;
            } else {
                $group[$val] ++;
            }
        }
        $total = count($res);
        $comgoodsids = '';
        foreach ($res as $val) {
            $comgoodsids .= $val['comgoodsid'] . ',';
        }
        $comgoodsids = trim($comgoodsids, ',');
        $sql = 'select id,price from `' . sh . '_comgoods` where 1=1 and id in (' . $comgoodsids . ')';
        $price = $this->pdo->fetchAll($sql);
        $allprice = 0;
        foreach ($price as $val) {
            $allprice += $group[$val['id']] * $val['price'];
        }
        //调试Ajax数据日志
        /* $file = '../222222.txt';
          //$content = var_export($res,true);
          $content = $allprice.'=='.$total;
          file_put_contents($file, $content,FILE_APPEND); */
        $back['carprice'] = $allprice / 100;
        $back['carcount'] = $total;
        $this->j['car'] = $back;

        /* 根据用户的选择，更新cookie的值 */
        $this->updatepro($doors);
    }

    function updatepro($doorstr) {
        //$savetime = time() + 3600;
        $savetime = 0;
        /* _cartbuy用于保存用户选择要去结账的doorid
          _cart用于保存用户从商品界面加入购物车的doorid
          proinfo用于保存购物车里的商品
          probuy用于保存用户选择要购买的商品
         */
        //setcookie(CacheName.'_cartbuy',$doorstr,$savetime);
        //setcookie(CacheName.'_cart',$doorstr,$savetime);
        $sql = 'select d.id as doorid,d.mystatus,d.doorstatus,d.comid,d.ic as dic,d.hasgoods as hasgood,cg.id as comgoodsids,g.id as gids,g.title,g.ic as gic,g.preimg,cg.price,cg.commission,d.placeid,d.deviceid from `' . sh . '_door` as d,`' . sh . '_comgoods` as cg,`' . sh . '_goods` as g';
        $sql .= ' where d.goodsid=g.id and g.id=cg.goodsid and d.id in (' . $doorstr . ')';
        $sql .= ' and d.comid=cg.comid';
        $sql .= ' group by g.id';
        $list = $this->pdo->fetchAll($sql);
        $a = json_encode($list);
        setcookie(CacheName . 'probuy', $a, $savetime);
    }

}

$myapi = new myapi();
unset($myapi);

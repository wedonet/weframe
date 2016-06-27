<?php

/* 余额支付 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
require_once syspath . '_inc/cls_door.php';
require_once syspath . '_inc/cls_biz.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();



        /* 检测权限 */
        if (!$this->haspower()) {
            $this->output();
            return false;
        }


        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
            case 'dopay':
                $this->dopay();
                $this->output();
                break;
        }
    }

    /**/

    function pagemain() {
        /* 提取定单总价 */
        $orderid = $this->main->rqid('orderid');

        /* 取定单总价 */
        $sql = 'select allprice from `' . sh . '_order` where 1 ';
        $sql .= ' and id=:orderid';
        $sql .= ' and uid=:uid';

        $para[':orderid'] = $orderid;
        $para[':uid'] = $this->main->user['id'];

        $result = $this->pdo->fetchOne($sql, $para);

        if (false == $result) {
            $this->j['allprice'] = 0;

            $this->ckerr('没找到这个定单');
            return false;
        } else {
            $this->j['allprice'] = $result['allprice'];
        }



        /* 取余额 */
        $sql = 'select acanuse from `' . sh . '_user` where 1 ';
        $sql .= ' and id=:uid';

        $result = $this->pdo->fetchOne($sql, Array(':uid' => $this->main->user['id']));

        if (false == $result) {
            $this->j['acanuse'] = 0;
        } else {
            $this->j['acanuse'] = $result['acanuse'];
        }
        unset($sql);
        unset($para);
        //判断设备状态，可能多个设备
        $sql = 'select mystatus  ';
        $sql .= ' from   `' . sh . '_device` ';
        $sql .= ' where id in(select deviceid  from `' . sh . '_order` ';
        $sql .= ' where id=:orderid';
        $sql .= ' and uid=:uid)';
        $para[':orderid'] = $orderid;
        $para[':uid'] = $this->main->user['id'];
        $a_door = $this->pdo->fetchAll($sql, $para);
        foreach ($a_door as $v) {
            if ($v['mystatus'] != "doing") {
                $this->j['devicemystatus'] = 'n';
                return;
            }
            $this->j['devicemystatus'] = 'y';
        }

        //取cookie的酒店信息  
        if (isset($_COOKIE[CacheName . '_comid'])) {
//die('a');
            $comid = $_COOKIE[CacheName . '_comid'];

            $this->main->getcominfo($comid);

            $this->j['company'] = $this->main->company;
            // print_r($a_door['comid']);die;     
        } else {
            die('b');
        }
        return true;
    }

    /* 检测权限
     * 返回 true,false
     */

    function haspower() {
        if ('user' == $GLOBALS['j']['user']['u_gic']) {
            $this->j['errcode'] = 0;
            return true;
        } else {
            $this->j['errcode'] = 1000;
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '已掉线，请重新登录！';
            return false;
        }
    }

    /* 执行余额支付 */

    function dopay() {
        $c_money = new cls_money();
        $c_biz = new cls_biz();
        $pdo = & $GLOBALS['pdo'];

        $mywayic = 'mymoney'; //支付方式

        $orderid = $this->main->rqid('orderid');

        /* 提取定单 */
        $sql = 'select * from `' . sh . '_order` where 1 ';
        $sql .= ' and id=:orderid ';
        //print_r($orderid);
        /* print_r($sql);die; */
        $a_order = $pdo->fetchOne($sql, Array(':orderid' => $orderid));

        if (false == $a_order) {
            $this->ckerr('没找到这个定单');
            return false;
        }

        /* 检测定单状态 */
        if (1 == $a_order['ispayed']) {
            $this->ckerr('这个定单已经支付过了');
            return false;
        }

        /* ==============================
         * 事务处理
         */
        try {
            $pdo->begintrans();

            /* 业务流程
             * 1. 检测柜门机是否有货，有一个门没货也取消
             * 2. 更新定单为已支付
             * 3. 更新柜门为已售完
             * 4. 更新柜门机商品数量
             * 5. 扣个人款
             * 6. 给商家入佣金
             * 7. 开门
             * 8. 更新定单状态为已提货
             * 9. 更新库存
             *              */

            /* 检测柜门是否是有货状态，如果有一个是无货的，也取消，把款打回用户账户(其实不用打回，因为还没扣款了) */
            if (!$this->doorhasgoods($a_order['doorids'])) {
                /* 更新定单为取消状态 */
                $sql = 'update `' . sh . '_order` set mystatus="cancel" where 1 ';
                $sql .= ' and id=:orderid';

                $pdo->doSql($sql, Array(':orderid' => $orderid));

                $this->ckerr('柜门中至少有一个是无货的');
                return false;
            }
            $openarr = explode(',', $a_order['doorids']);
            $opennum = count($openarr); //打开柜门数量

            /* 更新定单 */
            $sql = 'update `' . sh . '_order` set ispayed=1,payway="' . $mywayic . '",mystatus="payed" where 1 ';
            $sql .= ' and id=:orderid';

            $pdo->doSql($sql, Array(':orderid' => $orderid));


            /* 更新柜门已经售完 */
            if ($opennum > 1) {
                $sql = 'update `' . sh . '_door` set hasgoods=0,ischange=0 where 1';
                $sql .= ' and id in (' . $a_order['doorids'] . ')';
                $pdo->doSql($sql);
            } else {
                $sql = 'update `' . sh . '_door` set hasgoods=0,ischange=0 where 1';
                $sql .= ' and id=:doorid';
                $pdo->doSql($sql, Array(':doorid' => $a_order['doorids']));
            }


            /* 更新柜门机商品数量 */
            $sql = 'update `' . sh . '_device` set goodsnum=goodsnum-' . $opennum . ' where 1 ';
            $sql .= ' and id=:deviceid';
            $pdo->doSql($sql, Array(':deviceid' => $a_order['deviceid']));

            /* 财务 */
            $a['mywayic'] = 'mymoney';
            $a['title'] = $c_money->myway[$mywayic]['title'];
            $a['formcode'] = time() . $this->main->generate_randchar(6); //时间加六位随机码
            $a['uid'] = $a_order['uid'];
            $a['duid'] = $this->main->user['id'];
            $a['orderid'] = $orderid;
            $a['comid'] = $a_order['comid'];
             $a['myfrom'] = 'shendeng';
            /*      扣个人款项 */
            $a['action'] = 'substract';
            $a['mytype'] = 2010;
            $a['acceptgroup'] = 'user';
            $a['amoun'] = $a_order['allprice'];
            $result = $c_money->domoney($a);

            if (false == $result) {
                // $this->ckerr('给个人出款失败');
                $this->ckerr('余额不足，无法支付');
                return false;
            }

            /*      给商家入佣金 */
            $a['action'] = 'add';
            $a['mytype'] = 3010;
            $a['acceptgroup'] = 'com';
            $a['amoun'] = $a_order['commission'];

            $result = $c_money->domoney($a);

            //print_r($result);die;

            if (false == $result) {
                $this->ckerr('给商家入佣金失败');
                return false;
            }

            /* 开门 */
            $rs['deviceid'] = $a_order['deviceid'];
            $rs['doorid'] = $a_order['doorids'];

            $c_door = new cls_door($rs);

            //$c_door->opendoor('C89346C4EA0A', array(1,2,3,4,5,6,10,11,12,13,14,20,21,22));
            if (!$this->ckerr()) {
                return;
            }

            /* 更新定单状态和支付时间 */
            $sql = 'update `' . sh . '_order` set mystatus="taken", paytimeint=' . time() . ' where 1 ';
            $sql .= ' and id=:orderid';

            $pdo->dosql($sql, Array(':orderid' => $a_order['id']));

            /* 更新库存 */
            $result = $c_biz->updatestore($a_order);


            if (false == $result) {
                $this->ckerr('');
                return false;
            }

            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }




        $this->j['success'] = 'y';
    }

    /* 检测柜门里是否有货,
     * 全有货返回true else 返回 false
     */

    function doorhasgoods($doors) {
        $pdo = & $GLOBALS['pdo'];

        $sql = 'select count(*) from `' . sh . '_door` where 1 ';
        $sql .= ' and hasgoods=0 ';
        $sql .= ' and id in (:doors) ';

        $counts = $pdo->counts($sql, Array(':doors' => $doors));

        if ($counts > 0) {
            return false;
        } else {
            return true;
        }
    }

}

$myapi = new myapi();
unset($myapi);

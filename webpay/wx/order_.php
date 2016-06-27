<?php
require_once (__DIR__ . '/../../webpay/global.php');

require_once "../lib/WxPay.Api.php";
require_once '../lib/WxPay.Notify.php';
require_once 'log.php';

//Log::DEBUG("call back:" . json_encode($data));
//updateorder(195);
//outputxml();
/* 开门 */
$url = 'http://111.160.198.250:1615/api/door/door.php?deviceid=52&doorid=951&comid=131';

$contents = file_get_contents($url);

function updateorder($orderid) {
    $pdo = & $GLOBALS['pdo'];

    /* 提取定单 */
    $sql = 'select * from `' . sh . '_order` where 1 ';
    $sql .= ' and id=:orderid';

    $a_order = $pdo->fetchOne($sql, Array(':orderid' => $orderid));

    if (false == $a_order) {
        die('没找到这个定单');
    }

    /* 检测是不是已经支付过了 */
    if (1 == $a_order['ispayed']) {
        //Log::DEBUG("call back:" . $orderid.'已经支付过了');
        die('这个定单已经支付过了');
    }


    /* 事务处理 */
    try {
        $pdo->begintrans();

        /* 更新定单 */
        $sql = 'update `' . sh . '_order` set ispayed=1,payway="wx",mystatus="payed" where 1 ';
        $sql .= ' and id=:orderid';

        $pdo->doSql($sql, Array(':orderid' => $orderid));


        /* 更新柜门已经售完 */
        $sql = 'update `' . sh . '_door` set hasgoods=0 where 1';
        $sql .= ' and id=:doorid';

        $pdo->doSql($sql, Array(':doorid' => $a_order['doorids']));


        /* 更新柜门机数量 */
        $sql = 'update `' . sh . '_device` set goodsnum=goodsnum-1 where 1 ';
        $sql .= ' and id=:deviceid';
        $pdo->doSql($sql, Array(':deviceid' => $a_order['deviceid']));


        $c_money = new cls_money();
        $c_money->a_order = & $a_order;

        $c_money->domoneyin('10', '微信入款', $a_order['id'], '', $a_order); //1010
        $c_money->domoneyout('10', '微信支付', $a_order['id'], '', $a_order);
        ; //3010

        $pdo->submittrans();
    } catch (PDOException $e) {
        $pdo->rollbacktrans();
        echo ($e);
        die();
    }

    /* 开门 */


    //echo '更新成功';
    return true;
}

class cls_money {
    /* 充值 1010 */

    function domoneyin($myway, $title, $orderid, $formcode = '', $a_order) {
        $pdo = & $GLOBALS['pdo'];

        /* 用户款项操作 */

        $lastmytotal = $this->moneytotal($a_order['uid'], sh . '_moneyuser');


        $rs['uid'] = $a_order['uid'];
        $rs['unick'] = '';
        $rs['myvalue'] = $a_order['allprice'];
        $rs['myvalueout'] = 0;
        $rs['orderid'] = $orderid;
        $rs['title'] = $title;

        $rs['duid'] = $a_order['uid'];
        $rs['mytype'] = '1010';
        $rs['myway'] = $myway;
        $rs['formcode'] = $formcode;

        $rs['stimeint'] = time();
        $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);

        $rs['moneytype'] = 1;
        $rs['comid'] = $a_order['comid'];

        $rs['myip'] = getip();

        $rs['mytotal'] = $lastmytotal + $a_order['allprice'];
        $rs['myfrom'] = 'shendeng';
        $moneyid = $pdo->insert(sh . '_moneyuser', $rs);



        /* 更新用户余额 */
        if ($a_order['uid'] > 0) {/* 非注册用户购买 没有用户 */

            $sql = 'update `' . sh . '_user` set aall=aall+' . $a_order['allprice'] . ', acanuse=acanuse+' . $a_order['allprice'] . ', ain=ain+' . $a_order['allprice'] . ' where 1 ';
            $sql .= ' and id=:uid';
            $pdo->doSql($sql, Array(':uid' => $a_order['uid']));
        }
        //all acanuse ain



        /* 商家 */





        /* 平台 */
        unset($rs);

        $lastmytotal = $this->moneytotal(1, sh . '_moneyplat');

        $rs['uid'] = 1;
        $rs['unick'] = '';
        $rs['myvalue'] = $a_order['allprice'];
        $rs['myvalueout'] = 0;
        $rs['orderid'] = $orderid;
        $rs['title'] = $title;

        $rs['duid'] = $a_order['uid'];
        $rs['mytype'] = '1010';
        $rs['myway'] = $myway;
        $rs['formcode'] = $formcode;

        $rs['stimeint'] = time();
        $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);

        $rs['moneytype'] = 1;
        $rs['comid'] = $a_order['comid'];

        $rs['myip'] = getip();
        $rs['mytotal'] = $lastmytotal + $a_order['allprice'];
        $rs['myfrom'] = 'shendeng';
        $moneyid = $pdo->insert(sh . '_moneyplat', $rs);


        /* 更新用户余额 */
        $sql = 'update `' . sh . '_user` set aall=aall+' . $a_order['allprice'] . ', acanuse=acanuse+' . $a_order['allprice'] . ', ain=ain+' . $a_order['allprice'] . ' where 1 ';
        $sql .= ' and id=1';

        $pdo->doSql($sql);
    }

    /* 3010 */

    function domoneyout($myway, $title, $orderid, $formcode = '', $a_order) {
        $pdo = & $GLOBALS['pdo'];


        /* 用户款项操作 */
        $lastmytotal = $this->moneytotal($a_order['uid'], sh . '_moneyuser');

        $rs['uid'] = $a_order['uid'];
        $rs['unick'] = '';
        $rs['myvalue'] = 0;
        $rs['myvalueout'] = $a_order['allprice'];
        $rs['orderid'] = $orderid;
        $rs['title'] = $title;

        $rs['duid'] = $a_order['uid'];
        $rs['mytype'] = '3010';
        $rs['myway'] = $myway;
        $rs['formcode'] = $formcode;

        $rs['stimeint'] = time();
        $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);

        $rs['moneytype'] = 2;
        $rs['comid'] = $a_order['comid'];

        $rs['myip'] = getip();

        $rs['mytotal'] = $lastmytotal + $a_order['allprice'];
        $rs['myfrom'] = 'shendeng';
        $moneyid = $pdo->insert(sh . '_moneyuser', $rs);


        /* 更新用户余额 */
        $sql = 'update `' . sh . '_user` set aall=aall-' . $a_order['allprice'] . ', acanuse=acanuse-' . $a_order['allprice'] . ', ain=ain+' . $a_order['allprice'] . ' where 1 ';
        $sql .= ' and id=:uid';
        $pdo->doSql($sql, Array(':uid' => $a_order['uid']));

        //all acanuse ain



        /* ===========================================商家,给商家分佣金 */
        unset($rs);



        $lastmytotal = $this->commoneytotal($a_order['comid']);

        $rs['uid'] = $a_order['comid']; //供献人的uid
        $rs['unick'] = '';
        $rs['myvalue'] = $a_order['commission']; //佣金
        $rs['myvalueout'] = 0;
        $rs['orderid'] = $orderid;
        $rs['title'] = '佣金';

        $rs['duid'] = $a_order['uid'];
        $rs['mytype'] = '3030';
        $rs['myway'] = $myway;
        $rs['formcode'] = $formcode;

        $rs['stimeint'] = time();
        $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);

        $rs['moneytype'] = 1;
        $rs['comid'] = $a_order['comid'];

        $rs['myip'] = getip();

        $rs['mytotal'] = $lastmytotal + $a_order['allprice'];
        $rs['myfrom'] = 'shendeng';
        $moneyid = $pdo->insert(sh . '_moneycom', $rs);


        /* 更新店铺用户余额 */
        $sql = 'update `' . sh . '_commoneyaccount` set aall=aall+' . $a_order['commission'] . ', acanuse=acanuse+' . $a_order['commission'] . ' where 1 ';
        $sql .= ' and comid=:comid';
        $pdo->doSql($sql, Array(':comid' => $a_order['comid']));

        //all acanuse ain

        /* ==========================================平台，记录商家分的佣金,虚拟的不增加账户统计 */
        unset($rs);
        $rs['uid'] = $a_order['uid']; //哪个用户供献的
        $rs['unick'] = '';
        $rs['myvalue'] = 0; //佣金
        $rs['myvalueout'] = $a_order['commission'];
        ;
        $rs['orderid'] = $orderid;
        $rs['title'] = '佣金';

        $rs['duid'] = $a_order['uid'];
        $rs['mytype'] = '3030';
        $rs['myway'] = $myway;
        $rs['formcode'] = $formcode;

        $rs['stimeint'] = time();
        $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);

        $rs['moneytype'] = 2;
        $rs['comid'] = $a_order['comid'];

        $rs['myip'] = getip();
        $rs['myfrom'] = 'shendeng';
        $moneyid = $pdo->insert(sh . '_moneyplat', $rs);
    }

    /* 取用户上一笔的moneytotal */

    function moneytotal($uid, $tablename) {
        $pdo = & $GLOBALS['pdo'];

        $sql = 'select mytotal from `' . $tablename . '` where 1 ';
        $sql .= ' and id=:uid';
        $sql .= ' order by id desc limit 1';

        $result = $pdo->fetchOne($sql, Array(':uid' => $uid));

        if (false == $result) {
            return 0;
        } else {
            return $result['mytotal'];
        }
    }

    /* 取用户上一笔的moneytotal */

    function commoneytotal($comid) {
        $pdo = & $GLOBALS['pdo'];

        $sql = 'select mytotal from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and comid=:comid';
        $sql .= ' order by id desc limit 1';

        $result = $pdo->fetchOne($sql, Array(':comid' => $comid));

        if (false == $result) {
            return 0;
        } else {
            return $result['mytotal'];
        }
    }

    /* 跟据商家comid提取 uid */

    function getcomuid($comid) {
        $pdo = & $GLOBALS['pdo'];

        $sql = 'select uid from `' . sh . '_com` where 1 ';
        $sql .= ' and id=:comid';

        $result = $pdo->fetchOne($sql, Array(':comid' => $comid));

        return $result['uid'];
    }

}

function getip() {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if (!empty($_SERVER["REMOTE_ADDR"])) {
        $cip = $_SERVER["REMOTE_ADDR"];
    } else {
        $cip = '';
    }
    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);
    return $cip;
}

/* 检测还有没有商品 */

function hasgoods($doorid) {
    $pdo = & $GLOBALS['pdo'];

    $sql = 'select count(*) from `' . sh . '_door` where 1 ';
    $sql .= ' and hasgoods=1';
    $sql .= ' and id=:doorid';

    $counts = $pdo->counts($sql, Array('doorid' => $doorid));

    if (0 == $counts) {
        return false;
    } else {
        return true;
    }
}

function outputxml() {
    header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    //echo "<users><user><name>小小菜鸟</name><age>24</age><sex>男</sex></user><user><name>艳艳</name><age>23</age><sex>女</sex></user></users>";
    ?><xml>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <return_msg><![CDATA[OK]]></return_msg>
    </xml>
    <?php
}

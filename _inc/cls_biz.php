<?php

require_once('cls_money.php');
require_once syspath . '_inc/cls_door.php';
/* 神灯业务处理 */


/* 业务处理
 * 先更新定单，再更新财务，因为定单里有一些财务需要用到的信息
 */

class cls_biz {

    private $pdo;

    function __construct() {
        $this->pdo = & $GLOBALS['pdo'];
        $this->main = & $GLOBALS['main'];

        /* 定单状态 */
        $this->orderstatus = array(
            'new' => '待支付',
            'payed' => '已支付',
            'taken' => '交易成功',
            'cancel' => '取消'
        );

        /* 库存类型 */
        $this->storestatus = array(
            'in' => '入库',
            'out' => '出库',
            'delivery' => '发货',
            'sale' => '销售',
            'toplat' => '退回平台'
        );
    }

    /* $tradeid 支付的交易号 */
    /* $allprice 支付总价
     * $isactual 是否实际入款 true=实际入款
     * mywayic : wx alipay
     */

    function updateorder($orderid, $tradeid, $allprice, $mywayic, $isactual) {
        $pdo = & $GLOBALS['pdo'];

        /* 提取定单 */
        $sql = 'select * from `' . sh . '_order` where 1 ';
        $sql .= ' and id=:orderid';

        $a_order = $pdo->fetchOne($sql, Array(':orderid' => $orderid));

        if (false == $a_order) {
            $GLOBALS['errmsg'][] = '没找到这个定单';
            return false;
        }

        $c_money = new cls_money();

        /* 款项操作参数 */
        $a['mywayic'] = $mywayic;
        $a['title'] = $c_money->myway[$mywayic]['title'];
        $a['amoun'] = $allprice;
        $a['formcode'] = $tradeid;
        $a['uid'] = $a_order['uid'];
        $a['duid'] = $this->main->user['id'];
        $a['orderid'] = $orderid;
        $a['comid'] = $a_order['comid'];
        $a['myfrom'] = 'shendeng';

        if (true == $isactual) {

            /* 给用户充值 */
            $a['action'] = 'add';
            $a['mytype'] = 1010;
            $a['acceptgroup'] = 'user';


            $result = $c_money->domoney($a);

            if (false == $result) {
                $GLOBALS['errmsg'][] = '给用户入款失败';
                return false;
            }

            /* 给平台入款 */
            $a['action'] = 'add';
            $a['mytype'] = 5010;
            $a['acceptgroup'] = 'plat';


            $result = $c_money->domoney($a);

            if (false == $result) {
                $GLOBALS['errmsg'][] = '给平台入款失败';
                return false;
            }
        }


        /* 检测定单是不是已经支付过了 */
        if (1 == $a_order['ispayed']) {
            //Log::DEBUG("call back:" . $orderid.'已经支付过了');
            $GLOBALS['errmsg'][] = '这个定单已经支付过了';
            return false;
        }

        /* 检测柜门是否是有货状态，如果有一个是无货的，也取消，把款打回用户账户(其实不用打回，因为还没扣款了) */
        if (!doorhasgoods($a_order['doorids'])) {
            /* 更新定单为取消状态 */
            $sql = 'update `' . sh . '_order` set mystatus="cancel" where 1 ';
            $sql .= ' and id=:orderid';

            $pdo->doSql($sql, Array(':orderid' => $orderid));

            $GLOBALS['errmsg'][] = '柜门中至少有一个是无货的';
            return false;
        }
        $openarr = explode(',', $a_order['doorids']);
        $opennum = count($openarr);


        /* 更新定单 */
        $sql = 'update `' . sh . '_order` set ispayed=1,payway="' . $mywayic . '",mystatus="payed",paytimeint=' . time() . ' where 1 ';
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


        /* 更新柜门机数量 */
        $sql = 'update `' . sh . '_device` set goodsnum=goodsnum-' . $opennum . ' where 1 ';
        $sql .= ' and id=:deviceid';
        $pdo->doSql($sql, Array(':deviceid' => $a_order['deviceid']));


        /* 给用户执行扣款 */
        $a['action'] = 'substract';
        $a['mytype'] = 2010;
        $a['acceptgroup'] = 'user';

        $result = $c_money->domoney($a);

        if (false == $result) {
            $GLOBALS['errmsg'][] = '给个人出款失败';
            return false;
        }

        /* 给商家入佣金 */
        $a['action'] = 'add';
        $a['mytype'] = 3010;
        $a['acceptgroup'] = 'com';
        $a['amoun'] = $a_order['commission'];

        $result = $c_money->domoney($a);

        if (false == $result) {
            $GLOBALS['errmsg'][] = '给商家入佣金失败';
            return false;
        }

        /* 开门 */
        // if ($opennum > 1) {
        // $url = weburl . '/api/door/door.php?deviceid=' . $a_order['deviceid'] . '&dooridlist=' . $a_order['doorids'] . '&comid=' . $a_order['comid'] . '&orderid=' . $orderid;
        // } else {
        //  $url = weburl . '/api/door/door.php?deviceid=' . $a_order['deviceid'] . '&doorid=' . $a_order['doorids'] . '&comid=' . $a_order['comid'] . '&orderid=' . $orderid;
        //}
        $rs['deviceid'] = $a_order['deviceid'];
        $rs['doorid'] = $a_order['doorids'];
        $c_door = new cls_door($rs);

        if (count($GLOBALS['errmsg']) > 0) {
            return false;
        }


        //$contents = file_get_contents($url);

        /* 更新定单状态 */
        $sql = 'update `' . sh . '_order` set mystatus="taken" where 1 ';
        $sql .= ' and id=:orderid';

        $pdo->dosql($sql, Array(':orderid' => $a_order['id']));

        /* 更新库存 */
        $result = $this->updatestore($a_order);

        if (false == $result) {
            if (count($GLOBALS['errmsg']) > 0) {
                return false;
            }
        }


        //echo '更新成功';
        return true;
    }

    /* 跟据用户id,查找用户，返回数组
     * 
     */

    function getuserbyid($uid, $field = '*') {
        $sql = 'select ' . $field . ' from `' . sh . '_user` where 1 ';
        $sql .= ' and isdel=0 ';
        $sql .= ' and id=:id';

        return $this->pdo->fetchOne($sql, Array(':id' => $uid));
    }

    /* 提取定单的商品信息
     * 传入定单号，返回商品信息数组
     *      */

    function getordergoods($orderid, $backtype = null) {

//        $sql = 'select ordergoods.*,door.title as doortitle from `' . sh . '_ordergoods` as ordergoods';
//        $sql .= ' left join `' . sh . '_door` as door on ordergoods.doorid = door.id ';
//        $sql .= 'where 1 ';
//        $sql .= ' and ordergoods.orderid=:orderid ';

        $sql = 'select * from `' . sh . '_ordergoods` where 1 ';
        $sql .= ' and orderid=:orderid ';

        $result = $this->pdo->fetchAll($sql, Array(':orderid' => $orderid));

        if ('json' == $backtype) {
            return json_encode($result);
        } else {
            return $result;
        }
    }

    /* 提取定单商品列表，存进定单表mygoods字段 */

    function updateordergoods($orderid) {
        $sql = 'select * from `' . sh . '_ordergoods` where 1 ';
        $sql .= ' and orderid=:orderid ';




        $result = $this->pdo->fetchAll($sql, Array(':orderid' => $orderid));

        $rs['mygoods'] = json_encode($result);

        $this->pdo->update(sh . '_order', $rs, 'id=:orderid', Array(':orderid' => $orderid));

        return true;
    }

    /* 售出商品, 更新销售数量
     * $gids : 商品列表 逗号分隔
     * $goodslist : 数组 采用实时统计办法  这个暂时不用
     */

    function updatesalenum($orderid) {
        $sql = 'select * from `' . sh . '_order` where 1 ';
        $sql .= ' and id=:orderid';
        $a_order = $this->pdo->fetchOne($sql, Array(':orderid' => $orderid));
//        print_r($order);
        $totle = array();
        $gids = $a_order['gids'];
        $gidss = explode(',', $gids);
        foreach ($gidss as $m) {
            if (isset($totle[$m])) {
                $totle[$m] ++;
            } else {
                $totle[$m] = 1;
            }
        }
        $goodsid = array_keys($totle);
        $goodinfo = 'select id,isgroup,mygroup from `' . sh . '_goods` where id in(' . implode(",", $goodsid) . ')';
        $goodinfos = $this->pdo->fetchAll($goodinfo);


        foreach ($goodinfos as $v) {
            if (!empty($v['mygroup'])) {
                $groupnum = $totle[$v['id']];
//                echo $v['id']."是组合商品，商品信息如下$groupnum<br/><pre>";
                $mygroup = json_decode($v['mygroup']);
//                print_r($mygroup);
//                echo '</pre><br/>';                
                foreach ($mygroup as $vs) {
                    if (isset($totle[$vs->id])) {
                        $totle[$vs->id]+=$vs->count * $groupnum;
                    } else {
                        $totle[$vs->id] = $vs->count * $groupnum;
                    }
                }
            }
        }
        foreach ($totle as $k => $v) {
            $sql = 'update `' . sh . '_goods` set salenum=salenum+' . $v . '  where id=:id';

            $rs = $this->pdo->doSql($sql, Array(':id' => $k));
//           if($rs){
//            print_r($rs);
//           echo "id : $k 成功更新 值为 $v <br/>";
//           }else{
//                print_r($rs);
//              echo "id : $k 无需更新<br/>";
//           }
        }
        /// exit;
    }

    /* 售出商品, 更新库存
     * $gids : 商品列表 逗号分隔
     * $goodslist : 数组
     */

    function updatestore($a_order) {
        $gids = $a_order['gids'];

        /* 提取平台商品,并变成以商品id为索引 */
        $sql = 'select id,isgroup,mygroup from `' . sh . '_goods` where id in(' . $gids . ')';
        $result = $this->pdo->fetchAll($sql);
        foreach ($result as $v) {
            $a_goods[$v['id']]['id'] = $v['id'];
            $a_goods[$v['id']]['isgroup'] = $v['isgroup'];
            $a_goods[$v['id']]['mygroup'] = $v['mygroup'];
        }

        /* 提取全部店铺商品变成以商品id为索引 */
        $sql = 'select id,goodsid from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and comid=' . $a_order['comid'];
        $result = $this->pdo->fetchAll($sql);
        foreach ($result as $v) {
            $a_comgoods[$v['goodsid']] = $v['id'];
        }


        /* 循环定单商品 */
        $goodslist = json_decode($a_order['mygoods'], true);

        foreach ($goodslist as $v) {
            if (0 == $a_goods[$v['goodsid']]['isgroup']) { //单品
                /* 更新平台总库存 */
                $sql = 'update `' . sh . '_goods` set inventoriessum=inventoriessum-' . $v['counts'] . ' where id=' . $v['goodsid'] . ';';
                $this->pdo->doSql($sql);

                /* 更新店铺库存 */
                $sql = 'update `' . sh . '_comgoods` set inventories=inventories-' . $v['counts'] . ' where id=' . $v['comgoodsid'] . ';';
                $this->pdo->doSql($sql);

                /* 增加一条出库记录 */
                $s = 'insert into `' . sh . '_comstore` (goodsid, comgoodsid, formcode, duid, dname, stime, stimeint, ';
                $s .= 'mycount, mytype, other, comid) values (';
                $s .= $v['goodsid'];
                $s .= ',' . $v['comgoodsid'];
                $s .= ',"定单:' . $a_order['id'] . '"'; //formcode
                $s .= ',' . $this->main->user['id']; //duid
                $s .= ',"' . $this->main->user['u_nick'] . '"'; //dname
                $s .= ',"' . date('Y-m-d H:i:s', time()) . '"'; //stime
                $s .= ',' . time();
                $s .= ',' . $v['counts']; //mycount
                $s .= ',"sale"'; //mytype

                $s .= ',""'; //other
                $s .= ',' . $a_order['comid'];
                $s .= ');';
                $sql = $s;
                $this->pdo->doSql($sql);
            } else { //组合
                /* 提取这个组合品的单品 */
                $mygroup = $a_goods[$v['goodsid']]['mygroup'];

                /* 增加出库记录 */
                $a_mygroup = json_decode($mygroup, true);

                foreach ($a_mygroup as $x) {
                    /* 更新平台总库存 */
                    $sql = 'update `' . sh . '_goods` set inventoriessum=inventoriessum-' . ($v['counts'] * $x['count']) . ' where id =' . $x['id'];
                    $this->pdo->doSql($sql);

                    /* 更新店铺库存 */
                    $sql = 'update `' . sh . '_comgoods` set inventories=inventories-' . ($v['counts'] * $x['count']) . ' where id =' . $a_comgoods[$x['id']];
                    $this->pdo->doSql($sql);

                    $s = 'insert `' . sh . '_comstore` (goodsid, comgoodsid, formcode, duid, dname, stime, stimeint, ';
                    $s .= 'mycount, mytype, other, comid) values (';
                    $s .= $x['id'];
                    $s .= ',' . $a_comgoods[$x['id']];
                    $s .= ',"定单:' . $a_order['id'] . '"'; //formcode
                    //$s .= ',"定单:' . $v['id'] . '"'; //formcode
                    $s .= ',' . $this->main->user['id']; //duid
                    $s .= ',"' . $this->main->user['u_nick'] . '"'; //dname
                    $s .= ',"' . date('Y-m-d H:i:s', time()) . '"'; //stime
                    $s .= ',' . time();
                    $s .= ',' . $v['counts'] * $x['count']; //mycount
                    $s .= ',"sale"'; //mytype

                    $s .= ',""'; //other
                    $s .= ',' . $a_order['comid'];
                    $s .= ');';
                    $sql = $s;

                    $this->pdo->doSql($sql);
                }
            }
        }

        return true;
    }

    /* 跟据定单id列取取定单
     * $idlist:定单id列表， 1，2，3
     *      */

    function getordersbyidlist($idlist) {
        $sql = 'select * from `' . sh . '_order` where id in(' . $idlist . ')';
        $result = $this->pdo->fetchAll($sql);

        if (false == $result) {
            return $result;
        }

        foreach ($result as $v) {
            $a[] = $v;
        }
        return $a;
    }

}

/* 其它几个常用函数 */

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

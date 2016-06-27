<?php

/* 扫购 */

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'bying/_main.php'; //扫购通用数据

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->init();



        switch ($this->act) {
            default:
                $this->main();
                $this->output();
                break;
        }
    }

    function init() {

        $doorid = $this->main->rqid('d');

        if ($doorid < 0) {
            $doorid = $this->main->rqid('doorid');
        }

        /* 如果没有doorid then从cookie提，if连cookie里都没有 那么只能提示重新扫码了
         *  else存进cookie里一个d 代表扫进来的doorid */
        if ($doorid < 0) {

            if (isset($_COOKIE[CacheName . '_d'])) {
                //die('a');
                $doorid = $_COOKIE[CacheName . '_d'];

                if (!is_numeric($doorid)) {
                    $doorid = -1;
                } else {
                    $doorid = $doorid * 1;
                }
            } else {
                //die('b');
                $doorid = -1;
            }
        }

        /* if doorid>0 then 存进cookie, else提示请重新扫码 */
        if ($doorid > 0) {
            setcookie(CacheName . '_d', $doorid, time() + 3600 * 24 * 30);
        } else {
            $this->ckerr('请重新扫码进入');
            return false;
        }


        $this->doorid = $doorid;

        if (isset($GLOBALS['config']['message'])) {
            $this->j['message'] = $GLOBALS['config']['message'];
        }

        /* 提取门的信息 */
        $sql = 'select * from `' . sh . '_door` where 1 ';
        $sql .= ' and id=:doorid';

        $a_door = $this->pdo->fetchOne($sql, Array(':doorid' => $doorid));
        $this->a_door = & $a_door;

        if (false == $a_door) {
            showerr('柜门号错误!');
        }

        $this->door = & $a_door;
        $this->comid = & $a_door['comid'];
        
        setcookie(CacheName . '_comid', $a_door['comid'], time() + 3600 * 24 * 30, '/');//将店铺信息存进cookie
        
        
        //判断设备是否在线
        unset($sql);
        $sql = 'select device.mystatus  ';
        $sql .= ' from `' . sh . '_door` as door ';
        $sql .= ' inner join  `' . sh . '_device` as device  ';
        $sql .= ' on device.id=door.deviceid ';
        $sql .= ' where door.id=:doorid';
        $a_door1 = $this->pdo->fetchOne($sql, Array(':doorid' => $doorid));

        if ($a_door1['mystatus'] == 'doing') {
            $this->j['devicemystatus'] = 'y';
        } else {
            $this->j['devicemystatus'] = 'n';
        }
        $this->c_bying = new cls_bying();
        $this->c_bying->getcompany($this->comid);
    }

    function main() {
        /* 提取设备信息 */
        //$sql = 'select * from `'.sh.'_device` where 1 ';
        //$sql .= ' and id=:deviceid ';
        //$result = $this->pdo->fetchOne($sql, Array(':deviceid'=>$this->door['deviceid']));     
        //$this->j['device'] = $result;
        /*init如果有错误 then退出*/
        if(count($GLOBALS['errmsg'])>0){
            return false;
        }

        /* 取当前柜门的商品信息 */
        $sql = 'select goods.title as goodstitle, goods.bigimg, goods.readme, goods.content ';
        $sql .= ' ,comgoods.price ';
        $sql .= ' from `' . sh . '_comgoods` as comgoods ';
        $sql .= ' left join `' . sh . '_goods` as goods on comgoods.goodsid=goods.id '; //从comgoods提价格
        $sql .= ' where 1 ';
        $sql .= ' and comgoods.id=:comgoodsid';


        $result = $this->pdo->fetchOne($sql, Array(':comgoodsid' => $this->door['comgoodsid']));
        if (false == $result) {
            showerr('没找到这个商品信息！');
        }

        /* 再加点其它信息进去 */
        $result['hasgoods'] = $this->door['hasgoods'];
        $result['doorid'] = $this->doorid;

        $GLOBALS['j']['detail'] = $result;  //当前商品信息

        /* 存进日志 */
        $log['mytype'] = 'goodshit';
        $log['doorid'] = $this->doorid;
        $log['comid'] = $this->comid;
        $log['placeid'] = $this->a_door['placeid'];
        $log['deviceid'] = $this->a_door['deviceid'];
        $log['goodsid'] = $this->a_door['goodsid'];
        $log['comgoodsid'] = $this->a_door['comgoodsid'];
        $log['price'] = $result['price'];
        $this->main->dolog($log);


        /* 取格子+商品信息 */
        $sql = 'select door.*,goods.title as goodstitle,goods.preimg ';
        $sql .= ' ,comgoods.price ';
        $sql .= ' ,door.hasgoods,door.id as doorid ';
        $sql .= ' from `' . sh . '_door` as door ';
        $sql .= ' left join `' . sh . '_goods` as goods on door.goodsid=goods.id '; //从goods提图片和描述
        $sql .= ' left join `' . sh . '_comgoods` as comgoods on door.comgoodsid=comgoods.id '; //从comgoods提价格
        $sql .= ' where 1 ';
        $sql .= ' and deviceid=:deviceid';
        $sql .= ' order by door.id asc ';

        $result = $this->pdo->fetchAll($sql, Array(':deviceid' => $this->door['deviceid']));

        $GLOBALS['j']['list'] = $result;


        $clear = isset($_GET['clear']) ? $_GET['clear'] : '';
        
        /* 购买后返回首页，根据session来判断柜门是否开启$_SESSION[$orderid] = 'open'为开启了 */
        //$orderid = $this->main->rqid('orderid');
        //$orderid > 0 说明为购买返回的链接
        //if ($orderid > 0) {
        //    $sql = 'select doorstatus from `' . sh . '_order` where 1';
        //    $sql .= ' and id=' . $orderid;
        //    $ans = $this->pdo->fetchOne($sql);
        //    if ($ans['doorstatus'] == 'close') {
        //        $this->doorfix($orderid);
        //    }
        //}
        /* 购物车 */
        $this->j['car'] = $this->getcar($clear);
    }

    function doorfix($orderid) {
        $sql = 'select d.ic,o.doorids from `' . sh . '_order` as o,`' . sh . '_device` as d where 1=1';
        $sql .= ' and o.id=' . $orderid;
        $sql .= ' and o.deviceid=d.id';
        $res = $this->pdo->fetchOne($sql);
        $deviceic = $res['ic'];
        $doorids = $res['doorids'];
        $sql = 'select title from `' . sh . '_door` where 1 and id in (' . $doorids . ')';
        $titles = $this->pdo->fetchAll($sql);
        $titletemp = array_column($titles, 'title');
        $titlestr = implode(',', $titletemp);
        //将此设备已经在维修队列的柜门筛选出去
        $sql = 'select door from `' . sh . '_failtofix` where isend=0 and deviceic=:deviceic';
        $sql .= ' and door in (' . $titlestr . ')';
        $ha = $GLOBALS['pdo']->fetchAll($sql, Array(':deviceic' => $deviceic));
        $temp = array(); //需要被释放的值
        foreach ($ha as $val) {
            $temp[] = $val['door'];
        }
        $temp = array_unique($temp);
        $hb = array_diff($titletemp, $temp);
        if (empty($hb)) {
            $sql = 'update `' . sh . '_order` set doorstatus="fix" where id=' . $orderid;
            $orid = $this->pdo->doSql($sql);
            return;
        }

        $sql = 'select c.title as comname,c.id as comid,c.mylocation as address,c.telfront as tel,p.building,p.floor,p.title,p.id as placeid from `' . sh . '_device` as d,`' . sh . '_com` as c,`' . sh . '_place` as p where d.comid=c.id';
        $sql .= ' and d.ic=:device';
        $sql .= ' and d.placeid=p.id';
        $rs = $this->pdo->fetchOne($sql, Array(':device' => $deviceic));

        $info = $rs;
        $info['deviceic'] = $deviceic;
        $info['type'] = '门锁问题pay';
        $info['stimeint'] = time();
        $info['stime'] = date('Y-m-d H:i:s', $info['stimeint']);
        foreach ($hb as $v) {
            $info['door'] = $v;
            $backid = $this->pdo->insert(sh . '_failtofix', $info);
        }

        $sql = 'update `' . sh . '_order` set doorstatus="fix" where id=' . $orderid;
        $orid = $this->pdo->doSql($sql);
    }

    function getcar($a = '') {
        if ('clear' == $a) {

            if (isset($_COOKIE[CacheName . '_cart'])) {
                setcookie(CacheName . '_cart', '', -9999);
            }
            if (isset($_COOKIE[CacheName . 'proinfo'])) {
                setcookie(CacheName . 'proinfo', '', -9999);
            }
            if (isset($_COOKIE[CacheName . 'probuy'])) {
                setcookie(CacheName . 'probuy', '', -9999);
            }
            $car['carnums'] = '0';
            $car['doorids'] = '';
            return $car;
        }
        $a_car = [];

        /* 提取购物车 */
        if (isset($_COOKIE [CacheName . '_cart'])) {
            $cart = $_COOKIE [CacheName . '_cart'] . '';

            /* 检测cart里的doorid都是数字 */
            if ('' != $cart) {

                $a_car = array_unique(explode(',', $cart));
                $cart = implode(',', $a_car);

                foreach ($a_car as $v) {
                    if (is_numeric($v)) {
                        if (!is_int($v * 1)) {
                            $cart = '';
                            break;
                        }
                    }
                }
            }
        } else {
            $cart = '';
        }

        if ('' == $cart) {
            $car['carnums'] = '0'; //购物车商品数量
            $car['doorids'] = ''; //购物车doorid列表
        } else {
            $car['carnums'] = count($a_car);
            $car['doorids'] = $cart;
        }
        return $car;
        //setcookie(CacheName . 'uname', $u_name, time() + 3600 * 24 * 30, '/');
    }

}

$myapi = new myapi();
unset($myapi);

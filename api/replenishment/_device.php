<?php

/* 列出所有格子的商品情况,并进行补货换货等操作 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'replenishment/_main.php'; //补货页通用数据
//require_once 'power.php'; //补货页通用数据
require_once syspath . '_inc/cls_door.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain(); //quanxian

        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }


        switch ($this->act) {
            case '':
                $this->main();
                $this->output();
                break;
            case 'openall': //打开全部缺货的门
                $this->openall();
                $this->output('json');
                break;
            case 'change':
                $this->change();
                $this->output('json');
                break;
        }
    }

    /**/

    function main() {
        $placeid = $this->main->rqid('placeid');
        $deviceid = $this->main->rqid('deviceid');
        $comid=$this->j['user']['comid'];       

        /* 提取位置 */
        $sql = 'select * from `' . sh . '_place` where 1 ';
        $sql .= ' and id=:placeid ';
        $sql .= ' and comid= '.$comid;

        $result = $this->pdo->fetchOne($sql, Array(':placeid' => $placeid));
        if(empty($result)){
            $this->ckerr("参数错误！");
            return ;
        }

        $this->j['currentplace'] = $result;

        /* 取格子+商品信息 */
        $sql = 'select *,goods.title as goodstitle ';
        $sql .= ' ,comgoods.price ';
        $sql .= ' ,door.title as id ';
        $sql .= ' from `' . sh . '_door` as door ';
        $sql .= ' left join `' . sh . '_goods` as goods on door.goodsid=goods.id ';
        $sql .= ' left join `' . sh . '_comgoods` as comgoods on door.comgoodsid=comgoods.id ';
        $sql .= ' where 1 ';
        $sql .= ' and deviceid=:deviceid';
        $sql .= ' and door.comid='.$comid;
        $sql .= ' order by door.id asc ';

        $result = $this->pdo->fetchAll($sql, Array(':deviceid' => $deviceid));

        $GLOBALS['j']['list'] = $result;



//        for($i=0;$i<24;$i++){
//            $a[$i]['id'] = $i; //柜门id
//            $a[$i]['title'] = '门的名称';
//            $a[$i]['floor'] = '2'; //楼
//            $a[$i]['title'] = '101'; 
//            
//            $a[$i]['goodstitle'] = '商品名称';
//            $a[$i]['locationic'] = '位置ic';
//            $a[$i]['isless'] = 0; //是否缺货
//            $a[$i]['device'] = 'A12'; //设备编码
//            $a[$i]['hasgoods'] = 1;
//            
//        }
//        $GLOBALS['j']['list'] = $a;
    }

    /* 打开全部缺货的门 */

    function openall() {
        $deviceid = $this->main->rqid('deviceid');
        $comid = $this->main->rqid('comid');
        if($comid !=$this->j['user']['comid']){
            $this->ckerr("参数错误！");
            return ;
        }
       

        /* 提取位置信息 */
        $sql = 'select placeid from `' . sh . '_device` where 1 ';
         
        $sql .= ' and id=:deviceid';
        $result = $this->pdo->fetchOne($sql, Array(':deviceid' => $deviceid));
        
       
        //print_r($sql);die;
        if (false === $result) {
            $this->ckerr('没找到设备');
            
        } else {
            $placeid = $result['placeid'];
        }

        /* 提取所有需要打开的门 */
        $sql = 'select * from `' . sh . '_door` where 1 ';
        $sql .= ' and comid=:comid ';
        $sql .= ' and deviceid=:deviceid ';
        $sql .= ' and hasgoods=0 ';

        $result = $this->pdo->fetchAll($sql, Array(':deviceid' => $deviceid, ':comid' => $comid));

        /* 没有需要换货的，就不需要下面的操作了 */
        if (false == $result) {
            /* 日志 */
//            $log['doorids'] = '';
//            $log['doortitles'] = '';
//            $log['mytype'] = 'replenish';
//            $log['deviceid'] = $deviceid;
//            $log['placeid'] = $placeid;
//            $log['comgoodsids'] = '';
//
//            $this->log($log);

            return;
        }

        /* 提取打开的门和店铺商品 */
        foreach ($result as $v) {
            $doorids[] = $v['id'];
            $doortitles[] = $v['title'];
            $comgoodsids[] = $v['comgoodsid'];
        }


        /* 执行开门 */
        $rs['deviceid'] = $deviceid;
        $rs['doortitle'] = $doortitles;
    
        $c_door = new cls_door($rs);
        if (!$this->ckerr()) {
            return;
        }
        /* ==============================
         * 事务处理
         */
        $pdo = & $GLOBALS['pdo'];
        try {
            $pdo->begintrans();

            /* 开门成功后执行，把所有空的门改居有货 */
            $sql = 'update `' . sh . '_door` set hasgoods=1,ischange=0 where 1 ';
            $sql .= ' and comid=:comid ';
            $sql .= ' and deviceid=:deviceid ';
            $sql .= ' and hasgoods=0 ';

            $result = $this->pdo->doSql($sql, Array(':deviceid' => $deviceid, ':comid' => $comid));

            /* 更新这个设备为满货 */
            $sql = 'update `' . sh . '_device` set goodsnum=doornum where 1 ';
            $sql .= ' and comid=:comid ';
            $sql .= ' and id=:deviceid ';

            $result = $this->pdo->doSql($sql, Array(':deviceid' => $deviceid, ':comid' => $comid));
            

            /* 添加补换货日志 */
            /* 日志 */
            $log['doorids'] = join(',', $doorids);
            $log['doortitles'] = join(',', $doortitles);
            $log['mytype'] = 'replenish';
            $log['deviceid'] = $deviceid;
            $log['placeid'] = $placeid;
            $log['comgoodsids'] = join(',', $comgoodsids);

            $this->log($log);


            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['success'] = 'y';
    }

    /* 换货 */

    function change() {
        $deviceid = $this->main->rqid('deviceid');
        $comid = $this->main->rqid('comid');
        $placeid = $this->main->rqid('placeid');
        $doorid = $this->main->rqid('doorid');
        if($comid !=$this->j['user']['comid']){
            $this->ckerr("参数错误！");
            return ;
        }

        /* 提取a_door */
        $sql = 'select * from `' . sh . '_door` where 1 ';
        $sql .= ' and deviceid=:deviceid';
        $sql .= ' and comid=:comid';
        $sql .= ' and title=:title';

        $para[':deviceid'] = $deviceid;
        $para[':comid'] = $comid;
        $para[':title'] = $doorid;

        $a_door = $this->pdo->fetchOne($sql, $para);

        /* 执行开门 */
        // $url = weburl . '/api/door/door.php?deviceid=' . $deviceid . '&comid=' . $comid . '&doorid=' . $a_door['id'];
        // if (false == @file_get_contents($url)) {
        // $this->ckerr('神灯服务器连接失败');
        // }
        $rsa['deviceid'] = $deviceid;
        $rsa['doorid'] = $a_door['id'];
        // print_r($rs);die;
        $c_door = new cls_door($rsa,'1');
        if (!$this->ckerr()) {
            return;
        }

        //针对用户购买门未开，前台将物品给了用户，将是否换过货置1，用户不可以开此门
        //$rs['ischange'] = 1;
        //$this->pdo->update(sh . '_door', $rs, 'deviceid=' . $deviceid . ' and title=' . $doorid);

        /* 日志 */
        /*      提取商品列表 */
        $log['doorids'] = $a_door['id'];
        $log['doortitles'] = $a_door['title'];
        $log['mytype'] = 'change';
        $log['deviceid'] = $deviceid;
        $log['placeid'] = $a_door['placeid'];
        $log['comgoodsids'] = $a_door['comgoodsid'];

        $this->log($log);

        $this->j['success'] = 'y';
    }

    /*
     * $rs['doorids']  = 柜门id列表
     * $rs['doortitles'] = 柜门号列表
     * $rs['mytype']   = 类型 replenish, change
     * $rs['deviceid'  = 
     * $rs['placeid']  = 铺位id
     * $rs['comgoodsids'] = 店铺商品列表
     * 
     * 返回插入的记录id
     */

    function log($log) {
        $time = time();

        switch ($log['mytype']) {
            case 'replenish':
                $mytypename = '补货';
                break;
            case 'change':
                $mytypename = '换货';
                break;
            default:
                showerr('补换货日志类型错误');
                break;
        }

        /* 提取商品名称，生成列表 */
//        $goodstitles = '';     
//        if('' !== $log['comgoodsids']){
//            $sql = 'select title from `'.sh.'_goods` where 1 ';
//            $sql .= ' and id in(select goodsid from `'.sh.'_comgoods` where id in('.$log['comgoodsids'].'))' ;
//            
//            $result = $this->pdo->fetchAll($sql);
//            
//            if(false !== $result ){
//                foreach ($result as $v){
//                    $a[] = $v['title'];
//                }
//                $goodstitles = join(', ', $a);
//            }
//        }

        $historygoods = '';
        $goodstitles = '';
        if ('' != $log['doorids']) {
            $sql = 'select d.* ';
            $sql .= ' ,g.title as goodstitle, g.preimg as preimg ';
            $sql .= ' from `' . sh . '_door` as d ';
            $sql .= ' left join `' . sh . '_goods` as g on d.goodsid= g.id ';
            $sql .= ' where d.id in(' . $log['doorids'] . ')';

            $result = $this->pdo->fetchAll($sql);

            if (false !== $result) {
                $historygoods = json_encode($result);

                foreach ($result as $v) {
                    $a[] = $v['title'];
                }
                $goodstitles = join(', ', $a);
            }
        }

        $rs['uid'] = $this->main->user['id'];
        $rs['unick'] = $this->main->user['u_nick'];
        $rs['fullname'] = $this->main->user['u_fullname'];
        $rs['comid'] = $this->main->user['comid'];
        $rs['stime'] = date('Y-m-d H:i:s');
        $rs['stimeint'] = $time;

        $rs['doorids'] = $log['doorids'];
        $rs['doortitles'] = $log['doortitles'];
        $rs['mytype'] = $log['mytype'];
        $rs['mytypename'] = $mytypename;
        $rs['deviceid'] = $log['deviceid'];
        $rs['placeid'] = $log['placeid'];
        $rs['comgoodsids'] = $log['comgoodsids'];
        $rs['goodstitles'] = $goodstitles;

        $rs['historygoods'] = $historygoods; //补换货当时的商品信息，json 数组

        return $this->pdo->insert(sh . '_logcomreplenish', $rs);
    }

}

$myapi = new myapi();
unset($myapi);

<?php

/* 用户登录 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once (syspath . '_inc/cls_sms.php');


class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->json = Array(
            'err' => '',
            'success' => 'n'
        );


        switch ($this->act) {
            case 'offline': //设备掉线
                $this->stopinfo('offline');
                break;
            case 'online': //设备联接
                $this->stopinfo('online');
                break;
            case 'trouble': //整体倒
                $this->hastrouble();
                break;
            case 'locker': //开关门情况
                $this->lockerinfo();
                break;
        }

        echo json_encode($this->json);
    }

    /* 设备上线掉线 */

    function stopinfo($operate) {
        $device = $GLOBALS['main']->request('device', '设备号', 12, 12, 'char', 'invalid');

        if (count($GLOBALS['errmsg']) > 0) {
            $this->json['err'] = $GLOBALS['errmsg'];
            return false;
        }

        $dvcinfo = $this->getinfo($device);

        if (false === $dvcinfo) {
            $this->json['err'] = '没找到设备' . $device;
            return false;
        }

        //$sql = 'select * from `' . sh . '_device` where ic=:deviceic';
        //$result = $GLOBALS['pdo']->fetchOne($sql, Array(':deviceic' => $device));
        //if (false !== $result) {
        $this->updatafail($device, $dvcinfo, $operate);
        //}

        $this->json['success'] = 'y';
        if('online'==$operate){
            $this->json['mess'] = $device . '上线通知成功';
        }elseif('offline'==$operate){
            $this->json['mess'] = $device . '掉线通知成功';
        }
    }

    function updatafail($deviceic, $dvcinfo, $isstop) {
        if ('offline' == $isstop) {
            $isstop = 0;
        } else {
            $isstop = 1;
        }

        //掉线，添加一条错误记录，并更新设备状态为掉线
        if (0 == $isstop) {//0设备掉线
              unset($dvcinfo['isrun']);
            $dvcinfo['deviceic'] = $deviceic;
            $dvcinfo['door'] = 0;
            $dvcinfo['type'] = '心跳包异常';
            $dvcinfo['mytype'] = 'heart';
            $dvcinfo['mytypename'] = '掉线';
            $dvcinfo['stimeint'] = time();
            $dvcinfo['isend'] = 0;
            $dvcinfo['stime'] = date('Y-m-d H:i:s', $dvcinfo['stimeint']);

            $dvcinfo['comid'] = $dvcinfo['comid'];
            $dvcinfo['placeid'] = $dvcinfo['placeid'];

            $eee = $GLOBALS['pdo']->insert(sh . '_failtofix', $dvcinfo);

            $sqldevice = 'update `' . sh . '_device` set mystatus="down" where ic="' . $deviceic . '"';
            $faildevice = $GLOBALS['pdo']->doSql($sqldevice);

            return true;
        } else if (1 == $isstop) { //设备上线
            //设备上线，更新上一条记录的修复时间， 更新设备状态为doing
            $sqlsel = 'select * from `' . sh . '_failtofix` where deviceic=:deviceic and isend=0'; //提取没处理的错误报告
            $res = $GLOBALS['pdo']->fetchAll($sqlsel, Array(':deviceic' => $deviceic));

            foreach ($res as $v) {
                $sql = 'update `' . sh . '_failtofix` set isend=1 , repairtime="' . date('Y-m-d H:i:s', time()) . '" where type="心跳包异常" and id=' . $v['id'];
                $fail = $GLOBALS['pdo']->doSql($sql);
            }

            $sqldevice = 'update `' . sh . '_device` set mystatus="doing" where ic=:deviceic';
            $faildevice = $GLOBALS['pdo']->doSql($sqldevice, Array(':deviceic' => $deviceic));

            return true;
        }
    }

    /* 机体倾斜 */

    function hastrouble() {
        $device = $GLOBALS['main']->request('device', '设备号', 12, 12, 'char', 'invalid');
        //$fixinfo = $GLOBALS['main']->rqid('fixinfo');

        if (count($GLOBALS['errmsg']) > 0) {
            $this->json['err'] = $GLOBALS['errmsg'];
            return false;
        }

        $dvcinfo = $this->getinfo($device);

        if (false === $dvcinfo) {
            $this->json['err'] = '没找到';
            return false;
        }
        if ('0' == $dvcinfo['isrun']) {
            $this->json['err'] = '没运行';
            return false;
        }
        unset($dvcinfo['isrun']);
        $dvcinfo['deviceic'] = $device;
        $dvcinfo['door'] = 0;
        $dvcinfo['type'] = '机体倾斜';
        $dvcinfo['mytype'] = 'trouble';
        $dvcinfo['mytypename'] = '机体倾斜';
        $dvcinfo['stimeint'] = time();
        $dvcinfo['stime'] = date('Y-m-d H:i:s', $dvcinfo['stimeint']);
        $GLOBALS['pdo']->insert(sh . '_failtofix', $dvcinfo);
        $dvcinfo['comid'] = $dvcinfo['comid'];
        $dvcinfo['placeid'] = $dvcinfo['placeid'];
       
        /* 发送短信 */
        $a_sms = Array(
            //'13820997180',//张静
            '13612077208', //谢昕        
            '18830079639', //杨彬彬
             '18522085343', //刁彦泽   
             '18649070462', //万捷   
             '13820681641' //韩彬   
             
        );
        $c_sms = new cls_sms ();
        $content = $c_sms->getsendmsg(37);
        $contd= $dvcinfo['comname'].'，前台电话：'. $dvcinfo['tel'].',位置：'.$dvcinfo['building'].'-'.$dvcinfo['floor'].'-'.$dvcinfo['title'].',设备IC：'. $dvcinfo['deviceic'];
         $content = str_replace('{$activecode}',$contd, $content);
        foreach ($a_sms as $v) {
            $para['uid'] = 0;
            $para['comid'] = 0;
            $result = $c_sms->send($v, $content, $para);
           
            if (false == $result) {
                $this->ckerr('短信发送失败');
                $this->json['success'] = 'n';
                return false;
            }
        }
       
        $this->json['success'] = 'y';
    }

    function lockerinfo() {

        $device = $GLOBALS['main']->request('device', '设备号', 12, 12, 'char', 'invalid');
        $alllocker = $GLOBALS['main']->request('alllocker', '所有柜门号', 1, 255, 'char');
        $goodlocker = $GLOBALS['main']->request('goodlocker', '打开的柜门号', 1, 255, 'char', '', false); //允许为空所以是选填的
        $badlocker = $GLOBALS['main']->request('badlocker', '打开的柜门号', 1, 255, 'char', '', false); //允许为空所以是选填的
        //$success = $GLOBALS['main']->request('success','开门结果',0,2,'num','invalid');

        if (count($GLOBALS['errmsg']) > 0) {
            $this->json['err'] = $GLOBALS['errmsg'];
            return false;
        }

        /* if (1 === ($this->main->cache->get(CacheName . $device . 'doing'))) {
          $this->json['err'] = '正在打开柜门，请稍后操作';
          return false;
          } else {
          $this->main->cache->save(CacheName . $device . 'doing', 1);
          } */

        /* ==============================
         * 事务处理
         */
        try {
            $this->pdo->begintrans();

            /* 提取出一分钟内这个柜门机的定单，更新开门信息 */
            $myorder = $this->updatedeviceorder($device, $alllocker, $goodlocker, $badlocker);
            if (false == $myorder) {
                //return false;
            }


            //在维修队列中的柜门号被打开，更新记录isend
            if ('' !== $goodlocker) {
                $currenttime = time();
                $sql = 'update `' . sh . '_failtofix` set isend=1, repairtime="' . date('Y-m-d H:i:s', $currenttime) . '", repairtimeint=' . $currenttime . ' where 1 ';
                $sql .= ' and mytype="locker" ';
                $sql .= ' and isend=0 ';
                $sql .= ' and deviceic=:deviceic ';
                $sql .= ' and door in (' . $goodlocker . ')';

                unset($para);
                $para[':deviceic'] = $device;

                $GLOBALS['pdo']->doSql($sql, $para);
            }

            if ('' !== $badlocker) {

                $a_badlocker = explode(',', $badlocker);

                $info = $this->getinfo($device);
  unset($info['isrun']);
                $info['deviceic'] = $device;
                //$info['type'] = '门锁问题';
                $info['mytype'] = 'locker';
                $info['mytypename'] = '门锁未打开';
                $info['stimeint'] = time();
                $info['stime'] = date('Y-m-d H:i:s', $info['stimeint']);


                foreach ($a_badlocker as $v) {

                    $sql = 'select * from `' . sh . '_failtofix` where isend=0 ';
                    $sql .= ' and mytype="locker" ';
                    $sql .= ' and deviceic=:deviceic ';
                    $sql .= ' and door=:door';
                    $quer[':deviceic'] = $info['deviceic'];
                    $quer[':door'] = $v;

                    $resa = $GLOBALS['pdo']->fetchOne($sql, $quer);

                    if (false == $resa) {

                        $info['goodstitle'] = $this->getgoodstitle($device, $v)['title'];

                        $info['door'] = $v;

                        $backid = $GLOBALS['pdo']->insert(sh . '_failtofix', $info);
                    }
                }
            }
            $this->pdo->submittrans();
        } catch (PDOException $e) {
            $this->pdo->rollbacktrans();
            //$this->main->cache->delete($device);
            echo ($e);
            die();
        }

        // $this->main->cache->delete($device);

        $this->json['success'] = 'y';
    }

    /* 取设备
     * 
     *      */

    function getinfo($deviceic) {
//        $sql = 'select c.title as comname,c.mylocation as address,c.telfront as tel,p.building,p.floor,p.title ';
//        $sql .= ' from `' . sh . '_device` as d,`' . sh . '_com` as c,`' . sh . '_place` as p ';
//        $sql .= ' where p.comid=c.id';
//        $sql .= ' and d.ic=:device';
//        $sql .= ' and d.placeid=p.id';


        $sql = 'select c.title as comname,c.mylocation as address,c.telfront as tel ';
        $sql .= ' ,d.comid as comid, d.placeid as placeid,d.isrun as isrun ';
        $sql .= ' ,p.building,p.floor,p.title ';
        $sql .= ' from `' . sh . '_device` as d ';
        $sql .= ' left join `' . sh . '_com` as c on d.comid=c.id ';
        $sql .= ' left join `' . sh . '_place` as p on d.placeid=p.id ';
        $sql .= ' where p.comid=c.id';
        $sql .= ' and d.ic=:device';
        $sql .= ' and d.placeid=p.id';


        $res = $GLOBALS['pdo']->fetchOne($sql, Array(':device' => $deviceic));
       
        return $res;
    }

    //返回goods表title
    function getgoodstitle($deviceic, $door) {
        $sql = 'select  ';
        $sql .= ' goods.title';
        $sql .= ' from  `' . sh . '_goods` as goods ';
        $sql .= ' left join `' . sh . '_door` as door ';
        $sql .= ' on door.goodsid =goods.id';
        $sql .= ' where door.deviceic=:deviceic ';
        $sql .= ' and door.title=:door ';
        $quer[':deviceic'] = $deviceic;
        $quer[':door'] = $door;


        $res = $GLOBALS['pdo']->fetchOne($sql, $quer);

        return $res;
    }

    /* 更新定单开锁情况 
     * $alllocker, $goodlocker, $badlocker 字符串，用逗号分隔,是单片机传递进来的
     *      */

    function updatedeviceorder($deviceic, $alllocker, $goodlocker, $badlocker) {
        $a_alllocker = explode(',', $alllocker);

        if ('' === $goodlocker) {
            $a_goodlocker = [];
        } else {
            $a_goodlocker = explode(',', $goodlocker);
        }

        if ('' === $badlocker) {
            $a_badlocker = [];
        } else {
            $a_badlocker = explode(',', $badlocker);
        }

        /* 提取设备 */
        $sql = 'select * from `' . sh . '_device` where 1 ';
        $sql .= ' and ic=:deviceic';
        $a_device = $this->pdo->fetchOne($sql, Array(':deviceic' => $deviceic));
        if (false == $a_device) {
            $this->json['err'] = '没找到这个设备';
            return false;
        }

        /* 取三分钟内支付的，且包含这个锁的定单 */
        $sql = 'select * from `' . sh . '_order` where 1 ';
        $sql .= ' and deviceid=:deviceid';
        $sql .= ' and paytimeint>' . (time() - 180);
        $sql .= ' and doorstatus!="open" ';
        $sql .= ' and concat("," ,alllocker, ",") like :onelocker';
        $sql .= ' order by id desc '; //假如出现多条，取最后一条



        $para = [];
        $para[':deviceid'] = $a_device['id'];
        $para[':onelocker'] = '%,' . $a_alllocker[0] . ',%';

        $a_order = $this->pdo->fetchOne($sql, $para);

        if (false == $a_order) {
            $this->json['err'] = '没找到门锁对应的定单';
            return false;
        }

        /* 更新打开的锁和未打开的锁，如果打开的和没打开的总和跟所有门锁一样了，就更新定单的门锁状态为open */
        $a_order['alllocker'] = explode(',', $a_order['alllocker']);

        if ('' !== $a_order['goodlocker'] . '') {
            $a_order['goodlocker'] = explode(',', $a_order['goodlocker']);
        } else {
            $a_order['goodlocker'] = [];
        }

        //跟刚传进来的取并集（有重复）
        $a_order['goodlocker'] = array_merge($a_order['goodlocker'], $a_goodlocker);
        //去重复
        $a_order['goodlocker'] = array_unique($a_order['goodlocker']);
        //取所有门和好的门的差集
        $a_order['badlocker'] = array_diff($a_order['alllocker'], $a_order['goodlocker']);
        //print_r($a_order);die;
        /* if ('' !== $a_order['badlocker'] . '') {
          $a_order['badlocker'] = explode(',', $a_order['badlocker']);
          } else {
          $a_order['badlocker'] = [];
          } */



        /* 向定单的开关锁情况追加门号 */
        /* if (count($a_goodlocker) > 0) {
          foreach ($a_goodlocker as $v) {
          $a_order['goodlocker'][] = $v;
          }


          }

          if (count($a_badlocker) > 0) {
          foreach ($a_badlocker as $v) {
          $a_order['badlocker'][] = $v;
          }
          } */


        /* 整个定单是否完成提货 */
        if (count($a_order['alllocker']) == count($a_order['goodlocker']) + count($a_order['badlocker'])) {

            if (count($a_order['badlocker']) > 0) {
                /* echo '大于0'; */
                $rs['doorstatus'] = 'fix';
            } else {
                $rs['doorstatus'] = 'open';
                /* echo '没打于0'; */
            }
        }
        $rs['goodlocker'] = join(',', $a_order['goodlocker']);
        $rs['badlocker'] = join(',', $a_order['badlocker']);

        $this->pdo->update(sh . '_order', $rs, ' id=' . $a_order['id']);

        return true;
    }

}

$myapi = new myapi();
unset($myapi);

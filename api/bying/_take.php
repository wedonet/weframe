<?php

/* 提货页 */

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'bying/_main.php'; //扫购通用数据
require_once syspath . '_inc/cls_door.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        switch ($this->main->ract()) {
            case 'reopen':
                $_GET['outtype'] = 'json';
                $this->reopen();
                $this->output();
                break;
            default:
                $this->main();
                $this->output();
                break;
        }
    }

    function main() {
        $orderid = $this->main->rqid('orderid');

        /* 提取定单信息 */
        $sql = 'select * from `' . sh . '_order` where 1 ';
        $sql .= ' and id=:orderid';

        $result = $this->pdo->fetchOne($sql, Array(':orderid' => $orderid));
        $comid = $result['comid'];
        $c_bying = new cls_bying();
        $c_bying->getcompany($comid);
        $this->j['order'] = $result;
    }

    function reopen() {
        $orderid = $this->main->rqid('orderid');
        $sql = 'select * from `' . sh . '_order` where 1';
        $sql .= ' and id=:orderid';
        $res = $this->pdo->fetchOne($sql, Array(':orderid' => $orderid));

        if (false == $res) {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '没找到这个定单';
            return false;
        }

        /* 支付后超过两分钟的不能重新开门 */
        if ('' != $res['paytimeint']) {
            if (time() - $res['paytimeint'] > 120) {
                $this->j['success'] = 'n';
                $this->j['errmsg'][] = '支付后已超过两分钟，请联系前台送货';
                return false;
            }
        }

        //stop($res,true);
        $comid = $res['comid'];
        $doorlist = $res['doorids'];
        $deviceid = $res['deviceid'];

        //查询购买后柜门为无货状态的柜门,这些门可以由用户打开
        $sql = 'select id,title from `' . sh . '_door` where 1';
        $sql .= ' and id in (' . $doorlist . ')';
        $sql .= ' and hasgoods=0';
        $sql .= ' and ischange=0';
        $rs = $this->pdo->fetchAll($sql);


        //stop($rs,true);
        $candoorids = array_column($rs, 'id');
        $candoorstr = implode(',', $candoorids); //数组变字符串
        $cantitles = array_column($rs, 'title');
        $cantitlestr = implode(',', $cantitles);
        //stop($candoorstr);
        $opennum = count($candoorids);

        if (empty($candoorids)) {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '没有可以打开的门';
        } else {

            /* 开门 */
            //$url = weburl . '/api/door/_door.php?deviceid=' . $deviceid . '&dooridlist=' . $candoorstr . '&comid=' . $comid;
            //$contents = file_get_contents($url);
            $rs['deviceid'] = $deviceid;
            $rs['doortitle'] = $cantitles;
            $c_door = new cls_door($rs);

            //$c_door->opendoor('C89346C4EA0A', array(1,2,3,4,5,6,10,11,12,13,14,20,21,22));
            if (!$this->ckerr()) {
                return;
            }
            $this->j['success'] = 'y';
        }
    }

}

$myclassapi = new myapi();

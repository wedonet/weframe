<?php

/* 查货首页 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'patrol/_main.php'; //补货页通用数据
//require_once '_power.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain(); /* 检测权限类 */

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }


        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
        }
    }

    function pagemain() {

        $this->posttype = 'get';
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);


        $deviceid = $this->main->rqid('deviceid');

        /* 传回前端 */
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;

        /* get current place */
        $sql = 'select * from `' . sh . '_device` where id=' . $deviceid;

        $result = $this->pdo->fetchOne($sql);

        if (false !== $result) {
            $placeid = $result['placeid'];
        } else {
            $placeid = 0;
        }

        $sql = 'select * from `' . sh . '_place` where id=' . $placeid;
        $result = $this->pdo->fetchOne($sql);

        $this->j['currentplace'] = $result;
        if (!$this->ckerr()) {
            return false;
        }

        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }

        /* check date */
        if ('' == $date1) {
            $date1_int = strtotime(date('Y-m-d', (time())));
        } else {
            $date1_int = strtotime($date1);
        }

        if ('' == $date2) {
            $date2_int = strtotime(date('Y-m-d', time()));
        } else {
            $date2_int = strtotime($date2);
        }



        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);

//      $sql = 'select * from`'.sh.'_historygoods`';
        $sql = 'select * from `' . sh . '_logcomreplenish` as log ';

        //$sql .= ' left join `' . sh . '_place` as place on log.placeid=place.id ';
        $sql .= 'where 1 ';





        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';

        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);

        /* 加上所属店铺 */
        $sql .= ' and log.comid=:comid';
        $para[':comid'] = $this->main->user['comid'];

        /* 所属设备 */
        $sql .= ' and log.deviceid=:deviceid';
        $para[':deviceid'] = $deviceid;


        $sql .= ' order by log.id desc ';


        $result = $this->main->exers($sql, $para);
        $this->j['list'] = $result;
        


    }

}

$myapi = new myapi();
unset($myapi);

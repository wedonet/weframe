<?php

/* 列出所有格子的商品情况 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'replenishment/_main.php'; //补货页通用数据




/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        // require_once( ApiPath . 'tempvalue_rep.php' ); //取公共信息用户信息，
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        switch ($this->main->ract()) {
            case '':
                $this->pagemain();
                $this->output();
                break;

//         本页没用到开门和换货，故删之
//               case 'openall':
//                $this->openall();
//                $this->output();
//                break;      
//            case 'change':
//                $this->changegoods();
//                $this->output();
//                break;                   
        }
    }

    /**/

    function pagemain() {
        /*初始化list节点*/
        $this->j['list'] = false;
        
        /* select logcomreplenish.* ,place.title as placetitle
          from we_logcomreplenish as logcomreplenish,we_place as place
          where logcomreplenish.placeid=place.id
          and logcomreplenish.comid=144 */
      
       // print_r($placeid);die;
        $this->posttype = 'get';
        $placeid = $this->main->rqid('placeid');
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);
        $deviceid = $this->main->rqid('deviceid');
        
        
        /*get current place*/
        $comid = $this->main->user['comid'];
        $uid = $this->main->user['id'];
        
        $sql = 'select * ';
        $sql .= ' from ' . sh . '_place';
        $sql .= '  where comid= ' . $comid;
        $resultplace = $this->pdo->fetchAll($sql);
    
        $this->j['place'] = $resultplace;
        if (!$this->ckerr()) {
            return false;
        }
        
        
        
        
        
        /* 传回前端 */
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        if (!$this->ckerr()) {
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
        if ($date1_int > $date2_int) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }
        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);



        $sql = 'select logcomreplenish.* ,place.title as placetitle,place.id as pid ';
        $sql .= ' from ' . sh . '_logcomreplenish as logcomreplenish left join ' . sh . '_place as place';
        $sql .= ' on logcomreplenish.placeid=place.id  ';
        $sql .= '  where logcomreplenish.comid=:comid ';
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $sql .= ' and logcomreplenish.uid=:uid';
        if ($placeid > -1) {
            $sql .= ' and place.id=' . $placeid;
        }
        $sql .= ' order by logcomreplenish.id desc ';
        unset($para);
        $para[':comid'] = $comid;
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);
        $para[':uid'] = $uid ;
        //print_r($para);
        // print_r($sql);die;
        $result = $this->pdo->fetchAll($sql, $para);
        // print_r($result);die;
        $this->j['list'] = $result;
      
    }

}

$myapi = new myapi();
unset($myapi);

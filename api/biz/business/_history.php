<?php

/* 店铺商品统计 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'biz/_main.php';

/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
       
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        $this->act = $this->main->ract();
 //print_r(pr()) ;die;
        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;  
           
        }
    }
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
        $mytype = $this->main->request('mytype', '补换货类型', 1, 50, 'char', 'invalid', false);
        /*get current place*/
        $comid = $this->main->user['comid'];
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
            $date1_int = strtotime(date('Y-m-d', (time())))-7*24*3600;
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
        if (!$this->ckerr()) {
            return false;
        }

        $sql = 'select logcomreplenish.* ,place.title as placetitle,place.building,place.floor ';
        $sql .= ' from ' . sh . '_logcomreplenish as logcomreplenish left join ' . sh . '_place as place';
        $sql .= ' on logcomreplenish.placeid=place.id  ';
        $sql .= '  where logcomreplenish.comid=:comid ';
        $sql .= ' and logcomreplenish.stimeint>=:date1_int';
        $sql .= ' and logcomreplenish.stimeint<=:date2_int';
        if ($placeid > -1) {
            $sql .= ' and place.id=' . $placeid;
        }
         if (''!=$mytype) {
            $sql .= ' and logcomreplenish.mytype="' . $mytype.'"';
        }
        $sql .= ' order by logcomreplenish.id desc ';
        unset($para);
        $para[':comid'] = $comid;
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);
        //print_r($para);
       // print_r($sql);die;
        $result = $this->main->exers($sql, $para);
        // print_r($result);die;
        $this->j['list'] = $result;
        // $this->j['place']['title']=$result['placetitle'];
        // $this->j['place']['id']=$result['pid'];
    }

    /* 全部售卖品，包括已选择的平台商品和店铺自营 */

    function mylist() {    
          for ($i = 0; $i < 6; $i++) {
            $a[$i]['id'] = $i; //记录id
            $a[$i]['stimeint'] = '123456'; //操作时间
            $a[$i]['dnick'] = '小张'; //操作人昵称
            $a[$i]['duid'] = '356'; //操作人id
            $a[$i]['placetitle'] = '8888'; //日志表铺位名称，后台根据id去对应铺位表的title
            $a[$i]['doorids'] = '01,02,03,04,05,06,07,08'; //柜门号
            $a[$i]['mytypename'] = '补货'; //类型名称
            $a[$i]['building'] = 'A';//栋
            $a[$i]['floor'] = '3';//层
            $a[$i]['placetitle'] = '102';//位置名
            
        }
        $GLOBALS['j']['list'] = $a;
        
         unset($a); //清空上一次的a值
        for ($i = 0; $i < 3; $i++) {
            $a[$i]['id'] = $i; //铺位id         
            $a[$i]['title'] = '8408'; //铺位表铺位名称
        }
        $GLOBALS['j']['place'] = $a;
        
		 /*把搜索的数据传回去*/
		 $search['date1'] = '2016-2-6';
		 $search['date2'] = '2016-2-15';
		 
		 $this->j['search'] = $search;		 
       
    }
    
}

$myapi = new myapi();//建立类的实例
unset($myapi);//释放类占用的资源

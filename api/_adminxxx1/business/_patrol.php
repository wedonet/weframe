<?php

/* 定单管理 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . '_adminxxx1/_main.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();


        $this->modulemain = new cls_modulemain();

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
        $mytype = $this->main->request('mytype', '补换货类型', 1, 50, 'char', 'invalid', false);
         $comname=$this->main->request("comname","店铺名称",0,255,'char', 'invalid', false);
         $placetitle=$this->main->request("placetitle","商品名称",0,255,'char', 'invalid', false);

        /* 传回前端 */
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['comname'] = $comname;
        $this->j['search']['mytype'] = $mytype;        
        $this->j['search']['placetitle'] = $placetitle;
        if (!$this->ckerr()) {
            return false;
        }

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

        $sql = 'select l.*,c.title as comname ,p.title as placetitle from `' . sh . '_logcomreplenish` as l ';
        $sql .= ' left join `' . sh . '_com` as c on l.comid=c.id ';
        $sql .= ' left join `' . sh . '_place` as p on l.placeid=p.id ';
        $sql .= ' where 1 ';
        /* date */
        $sql .= ' and l.stimeint>=:date1_int';
        $sql .= ' and l.stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);
        
        if ('' != $mytype) {
            $sql .= ' and l.mytype="' . $mytype . '"';
        }
          if ('' != $comname) {
            $sql .= ' and c.title like "%' . $comname . '%"';
        }
         if ('' != $placetitle) {
            $sql .= ' and p.title like "%' . $placetitle . '%"';
        }
        $sql .= ' order by id desc ';

        $result = $this->main->exers($sql,$para);
    

        $this->j['list'] = $result;
        //print_r(  $this->j['list']);die;
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
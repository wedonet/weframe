<?php

require_once( __DIR__ . '/../../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once syspath . '_inc/cls_form.php';

require_once AdminApiPath . '_main.php'; //本模块数据
//die(AdminApiPath);

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
            case 'export':
                $this->pagemain();
                break;
        }
    }

    function pagemain() {

        /* 初始化list节点 */
        $this->j['list'] = false;
        
        $title = $this->main->request('title', 'title', 0, 255, 'char', 'invalid');
        $this->j['title'] = $title;
        
        $ic = $this->main->request('ic', 'ic', 1, 255, 'char', 'invalid');
        $this->j['ic'] = $ic;
        
        $this->posttype = 'get';
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

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


        $sql = 'select * ';
        $sql .= ' from ' . sh . '_form_' . $ic;
        $sql.=' where 1';
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $sql .= ' order by id desc ';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);
        //print_r($para);
        //print_r( $sql);die;
        if ('export' == $this->act) {
            $result = $this->pdo->fetchAll($sql, $para);
            $this->j['list'] = $result;
        } else if ('' == $this->act) {
            $result = $this->main->exers($sql, $para);
            $this->j['list'] = $result;
        }
    }

}

$myapi = new myapi();
unset($myapi);

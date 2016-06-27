<?php

require_once( __DIR__ . '/../../../global.php');
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

        $this->posttype = 'get';
        $title= $this->main->rqid('id');
        $ic = $this->main->request('ic', 'ic', 1, 255, 'char', 'invalid');
        $dates = $this->main->getdates(strtotime(date('Y-m-d', (time() - 7 * 24 * 3600))), strtotime(date('Y-m-d', time())));
       // print_r($title);
        if ($title >= 0) {
           $sql = 'select * ';
        $sql .= ' from `' . sh . '_form` ';
        $sql .= ' where 1';
        $sql .= ' and id=:id';
        $para[':id']=$title;
       // print_r($para);
       // print_r($sql);die;
       $result = $this->pdo->fetchOne($sql, $para);
     
          $this->j['form'] = $result;
          //print_r($this->j['form']);die;
        }
        /* 传回前端 */
     
        $this->j['ic'] = $ic;
        $this->j['search']['date1'] = $dates['date1'];
        $this->j['search']['date2'] = $dates['date2'];

        if (!$this->ckerr()) {
            return false;
        }

        $sql = 'select * ';
        $sql .= ' from `' . sh . '_form_' . $ic . '` ';
        $sql .= ' where 1';
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $sql .= ' order by id desc ';
        unset($para);
        $para[':date1_int'] = $dates['int1'];
        $para[':date2_int'] = $dates['int2'] + (24 * 3600);

       // print_r($para);
        //print_r($sql);die;
        unset($result);
        if ('export' == $this->act) {
            $result = $this->pdo->fetchAll($sql, $para);
        } else if ('' == $this->act) {
            $result = $this->main->exers($sql, $para);
        }

        $this->j['list'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

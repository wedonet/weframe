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
        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
			case 'edit':
                $this->getplace();
                $this->output();
                break;
			case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;  
           
        }
    }
 function pagemain() {
     /*初始化list节点*/
        $this->j['list'] = false;
        $this->posttype = 'get';
        $placeid = $this->main->rqid('placeid');
        $comid = $this->main->user['comid'];
//        $sql = 'select * ';
//        $sql .= ' from ' . sh . '_place';
//        $sql .= '  where comid= ' . $comid;
//	    $sql .= ' order by cls asc,id asc ';
//        $resultplace = $this->pdo->fetchAll($sql);
//    
//        $this->j['place'] = $resultplace;
        if (!$this->ckerr()) {
            return false;
        }
    
//	function mylist() {
        $sql = 'select * from `' . sh . '_place` where 1 ';
        $sql .= ' and comid=:comid';
        $sql .= ' order by cls asc,id asc ';

//        $result = $this->pdo->fetchAll($sql, Array(':comid' => $this->comid));
//
//        $this->j['list'] = & $result;
//    }

//        unset($para);
        $para[':comid'] = $comid;
        $result = $this->main->exers($sql, $para);
        $this->j['list'] = $result;
    }
	    function getplace() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_place` where 1 ';
        $sql .= ' and id=:id';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $GLOBALS['j']['data'] = & $result;
    }

	 function esave() {
        $id = $this->main->rfid();

        $this->main->posttype = 'post';

        //$rs['ic'] = $this->main->request('ic', 'IC', 2, 20, 'char', 'invalid');
       // $rs['title'] = $this->main->request('title', '名称', 1, 20, 'char', 'invalid');
       // $rs['building'] = $this->main->request('building', '栋', 1, 20, 'char', 'invalid');
       // $rs['floor'] = $this->main->request('floor', '层', 1, 200, 'int');

        $rs['cls'] = $this->main->request('cls', '排序', 0, 9999999, 'int');

        $this->ckerr();

        $this->pdo->update(sh . '_place', $rs, 'id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    /* 全部售卖品，包括已选择的平台商品和店铺自营 */

    function mylist() {    
          for ($i = 0; $i < 6; $i++) {
            $a[$i]['id'] = $i; //记录id
            $a[$i]['duid'] = '356'; //操作人id
            $a[$i]['placetitle'] = '8888'; //日志表铺位名称，后台根据id去对应铺位表的title
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
		 
		 $this->j['search'] = $search;		 
       
    }
    
}

$myapi = new myapi();//建立类的实例
unset($myapi);//释放类占用的资源

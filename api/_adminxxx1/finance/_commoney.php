<?php

/* 平台商品接口 */
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
            case 'export':
                $this->pagemain();
                $this->output();
                break;
        }
    }

    function pagemain() {
        /* 接收参数 */
        $this->main->posttype = 'get';

        $paymentstatus = $this->main->request('paymentstatus', '状态', 0, 3, 'int', '', false);
        $myfrom = $this->main->request('myfromname', '订单类型', 0, 255, 'char', '', false);
        $comname = $this->main->request('comname', '店铺名称', 1, 50, 'char', '', false);

        if (!$this->ckerr()) {
            return false;
        }

        $this->j['search']['comname'] = $comname;
        
           $this->j['search']['myfrom'] = $myfrom;
        $this->j['search']['paymentstatus'] = $paymentstatus;


        /* -------------------------------------- */
        $dates = $this->main->getdates(strtotime('2015-1-1'), strtotime(date('Y-m-d', time())));

        /* 传回前端 */
        $this->j['search']['date1'] = $dates['date1'];
        $this->j['search']['date2'] = $dates['date2'];

        if (!$this->ckerr()) {
            return false;
        }


        /* check com */
        $comidlist = '';
        if ('' != $comname) {
            $sql = 'select id from `' . sh . '_com` where title like :comname ';
            $result = $this->pdo->fetchAll($sql, Array(':comname' => '%' . $comname . '%'));
            if (false == $result) {
                $this->ckerr('没找到这个店铺');
                return false;
            } else {
                //$a_com = $result;
                $comidlist = join(',', array_column($result, 'id'));
            }
        }


        /* 店铺财务记录 */
        $sql = 'select m.*, o.mygoods as mygoods,com.title as comname,case when m.myfrom="shendeng" then "神灯订单" when m.myfrom="diannei" then "店内有售" end as myfromname ';
       $sql.=' from `' . sh . '_moneycom` as m ';
        $sql .= ' left join `' . sh . '_order` as o on m.orderid=o.id ';
        $sql .= ' left join `'.sh.'_com` as com on m.comid=com.id ';
        $sql .= ' where 1 ';
        $sql .= ' and m.myvalue>0 '; //只显示入款
         

        if ('' !== $paymentstatus . '') {
            $sql .= ' and paymentstatus =' . $paymentstatus; //还没申请提现的
        }
        $sql .= ' and m.mytype in(3010,3020) ';

        /* 加上所属店铺 */
        if ('' != $comidlist) {
            $sql .= ' and m.comid in (' . $comidlist . ')';
        }


        /* date */
        $sql .= ' and m.stimeint>=:date1_int';
        $sql .= ' and m.stimeint<=:date2_int';

        $para[':date1_int'] = $dates['int1'];
        $para[':date2_int'] = $dates['int2'] + (24 * 3600);
       if ('' !== $myfrom) {
          $sql .= ' and m.myfrom=:myfrom'; //神灯的
          $para[':myfrom']=$myfrom;
        }
 
        $sql .= ' order by m.id desc ';
  
//  $this->arrfrom = array(
//            'shendeng' => '神灯订单',
//            'diannei' => '店内有售'
//        );
  
        if ('export' == $this->act) {
            $this->j['list'] = $this->pdo->fetchAll($sql, $para);           
            return;
        } else {
            
            $result = $this->main->exers($sql, $para);
//            print_r($result);die;
//                foreach ($result as $v) {
//                $v['myfromname'] =  $this->arrfrom[$v['myfrom']];
//		}
		
             //$result['myfromname'] = $this->arrfrom[$result['myfrom']];
        }


        $this->j['list'] = $result;

        /* 提取统计 */
        unset($para);
        $sql = 'select sum(myvalue) as mysum, paymentstatus from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';

        $para[':date1_int'] = $dates['int1'];
        $para[':date2_int'] = $dates['int2'] + (24 * 3600);

        /* 搜店铺 */
        if ('' != $comidlist) {
            $sql .= ' and comid in (' . $comidlist . ')';
        }
  if ('' !== $myfrom) {
          $sql .= ' and myfrom=:myfrom'; //神灯的
          $para[':myfrom']=$myfrom;
        }
        
        $sql .= ' and mytype<>3030 ';
        $sql .= ' group by paymentstatus ';

        $result = $this->pdo->fetchAll($sql, $para);

        /* 用状态做索引 */
        $a = array();
        foreach ($result as $v) {
            $a[$v['paymentstatus']] = $v['mysum'];
        }

        if (!isset($a[0])) {
            $a[0] = 0;
        }
        if (!isset($a[1])) {
            $a[1] = 0;
        }
        if (!isset($a[2])) {
            $a[2] = 0;
        }
        if (!isset($a[3])) {
            $a[3] = 0;
        }

        $this->j['account'] = $a;

        //print_r($this->j);
        //die;
    }
  

}

$myapi = new myapi();
unset($myapi);

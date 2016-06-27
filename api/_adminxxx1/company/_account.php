<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';

require_once AdminApiPath . '_main.php';
require_once '_main.php'; /* 业务管理通用数据 */

/* */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        $this->comid = $this->main->rid('comid');

        $c_company = new cls_companymain();

        /* 店铺信息添加进$globals['j']['company'] */
        $this->j['company'] = $c_company->getcompany($this->comid);

        $this->act = $this->main->ract();

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;
            case'select';
                $this->selectorder();
                $this->output();
                break;
            case'creat';
                $this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getorder();
                $this->output();
                break;
            case 'nsave': //保存
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savenew();
                $this->output();
                break;
            case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;
        }
    }

    function mylist() {
        $c_money = new cls_money();
        $moneysetting = $c_money->moneysetting;
        $this->posttype = 'get';
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);
     $myfrom = $this->main->request('myfromname', '订单类型', 0, 255, 'char', '', false);
        /* 传回前端 */
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['comname'] = '';
         $this->j['search']['myfrom'] = $myfrom;
        if (!$this->ckerr()) {
            return false;
        }

        if ('' == $date1) {
             $date1_int = strtotime(date('Y-m-d', (time() - 7 * 24 * 3600)));
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

        $sql = 'select * ,case when myfrom="shendeng" then "神灯订单" when myfrom="diannei" then "店内有售" end as myfromname from `' . sh . '_moneycom` where 1';
        $sql .= ' and comid=:comid';
        $para[':comid'] = $this->comid;
        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);
     if ('' !== $myfrom) {
          $sql .= ' and myfrom=:myfrom'; //神灯的
          $para[':myfrom']=$myfrom;
        }

        $sql .= ' order by id desc';
//        print_r($sql);
//          print_r($para);die;
        $result = $this->main->exers($sql, $para);

        /* 格式化款项类型 */
        $i = 0;
        foreach ($result['rs'] as $v) {
            $result['rs'][$i]['mytypename'] = array_key_exists($v['mytype'], $moneysetting) ? $moneysetting[$v['mytype'] * 1] : '未知款项:' . $v['mytype'];
            $i++;
        }
//print_r($result);die;
        $this->j['list'] = $result;

        /* 统计 */
        unset($para);
        $sql = 'select sum(myvalue) as myvalue,sum(myvalueout) as myvalueout, count(*) as mycount from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and comid=:comid';
        $para[':comid'] = $this->comid;
        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
 if ('' !== $myfrom) {
          $sql .= ' and myfrom=:myfrom'; //神灯的
          $para[':myfrom']=$myfrom;
        }
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);

        $result = $this->pdo->fetchOne($sql, $para);
        $this->j['account'] = $result;
//		return $result;
//        for ($i = 0; $i < 10; $i++) {
//            $a[$i]['id'] = $i;//流水号
//            $a[$i]['uid'] = '1111';//操作人id
//            $a[$i]['unick'] = '小张';//操作人姓名
//            $a[$i]['myvalue'] = '200';//入款
//            $a[$i]['myvalueout'] = '200';//出款
//            $a[$i]['mytotal'] = '100';//余额
//            $a[$i]['orderid'] = '111';//订单id
//            $a[$i]['duid'] = '356';//操作人id
//            $a[$i]['dnick'] = '小张';//操作人昵称
//            $a[$i]['mytype'] = '定单支付';//款项类型
//            $a[$i]['myway'] = '在线支付';//支付方式
//            $a[$i]['stime'] = '2015-11-11';//时间
//            $a[$i]['other'] = '备注内容';//备注
//            $a[$i]['formdate'] = '2015-11-11';//备注
//        }
//        $this->j['data'] = $a;
//        $this->j['userlist']['rs'] = $a;
//        $this->j['userlist']['total'] = 100;
    }

    /* 提取平台的全设备 */

    function formedit() {
        
    }

    function myform() {
        
    }

    function getorder() {
        
    }

    function esave() {
        if (1 == 1) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
            $this->j['errinput'] = 'u_mobile';
        }
    }

    function savenew() {
        if (1 == 1) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
            $this->j['errinput'] = 'id,ic';
        }
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
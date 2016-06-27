<?php

/* 扫购,选择支付方式 */

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

    /* 检测权限 */
        if (!$this->haspower()) {
            $this->output();
            return false;
        }
        switch ($this->act ){
//            case 'pay':
//                $_POST['outtype'] = 'json'; //输出json格式
//                $this->pay();
//                $this->output();
//                break;
            default:
                $this->pagemain();
                break;
        }
    }
  function haspower() {
        if ('user' == $GLOBALS['j']['user']['u_gic'] || 'guest' == $GLOBALS['j']['user']['u_gic']) {
            $this->j['errcode'] = 0;
            return true;
        } else {
            $this->j['errcode'] = 1000;
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '已掉线，请重新登录！';
            return false;
        }
    }
    function pagemain() {
        //if (isset($_COOKIE[CacheName.'_cart'])) {

        $orderid = $this->main->rqid('orderid');

        $sql = 'select * from `' . sh . '_order` where 1 ';
        $sql .= ' and id=:orderid ';

        $result = $this->pdo->fetchOne($sql, Array(':orderid' => $orderid));

      
            
        $this->j['allprice'] = $result['allprice'];
        
        /*提取可用余额*/
        $a_mymoney = $this->getmoneyvalue();
        $this->j['acanuse'] = $a_mymoney['acanuse'];
        $this->j['vmoney'] = $a_mymoney['vmoney'];
        //}else{
        //    $this->j['allprice'] = 0;
        //}
        
            unset($sql);
            $sql = 'select device.mystatus  ';
            $sql .= ' from `' . sh . '_door` as door ';
            $sql .= ' inner join  `' . sh . '_device` as device  ';
            $sql .= ' on device.id=door.deviceid ';
            $sql .= ' where door.id in (' .$result['doorids'] . ')';
            $a_door = $this->pdo->fetchAll($sql);
          
            foreach ($a_door as $v)
            {
                if($v['mystatus']!="doing")
                {
                      $this->j['devicemystatus'] = 'n';
                      return;
                }
                 $this->j['devicemystatus'] = 'y';
            }
            
        //取cookie的酒店信息    
            if (isset($_COOKIE[CacheName . '_comid'])) {

            $comid = $_COOKIE[CacheName . '_comid'];

            $this->main->getcominfo($comid);

            $this->j['company'] = $this->main->company;
            // print_r($a_door['comid']);die;     
        }
    }
    
    function getmoneyvalue(){
        $sql = 'select acanuse,vmoney from `'.sh.'_user` where 1 ';
        $sql .= ' and id=:uid';
        $sql .= ' and isdel=0';
        
        $result = $this->pdo->fetchOne($sql, Array(':uid'=>$this->main->user['id']));
        
        if(false == $result){
            return 0;
        }else{
            return $result;
        }
    }

//    function pay(){
//        /*生成一个定单*/
//        $comgoodsid = $this->main->rqid('comgoodsid');
//        $doorid = $this->main->rqid('doorid');
//        $placeid = $this->main->rqid('placeid');
//        
//        $paytype = $this->main->ract('paytype');
//   
//         /*提取价格*/
//        $sql = 'select * from `'.sh.'_comgoods` where 1 ';
//        $sql .= ' and id=:comgoodsid ';
//        
//        $result = $this->pdo->fetchOne($sql, Array(':comgoodsid'=>$comgoodsid));
//        
//        
//        /*生成定单*/
//        $rs['allprice'] = $result['price'];
//        $rs['stimeint'] = time();
//        $rs['stime'] = date("Y-m-d H:i:s", $rs['stimeint']);
//        $rs['uid'] = $this->main->user['id'];
//        $rs['ispayed'] = 0;
//        $rs['mystatus'] = 'new';
//        $rs['gids'] = $comgoodsid;
//        $rs['payway'] = $paytype;
//        $rs['comid'] = $result['comid'];
//        $rs['placeid'] = $placeid;
//        
//        $orderid = $this->pdo->insert( sh.'_order', $rs );
//        
//        $this->j['success'] = 'y';
//        $this->j['orderid'] = $orderid;
//        $this->j['allprice'] = $result['price'];
//  
//        /*跟据支付方式跳转到支付页*/
//        //$orderid
//    }
}

$myapi = new myapi();
unset($myapi);

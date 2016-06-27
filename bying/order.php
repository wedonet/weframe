<?php

require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();




        /* ============================== */
        /* 什么情况下必须返回json格式 */

        $jsonact = array('getprice'
            , 'batch'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }
        require_once(ApiPath . 'bying/_order.php'); //访问接口去

     
        switch ($this->act) {
            case'':
                $this->main(); //主内容区
                break;
              /*case'other':
                $this->mainother(); //主内容区
                break;*/
        }
    }

    function mainother() {
       // print_r($_COOKIE[CacheName . 'proorderid']);die;
      if (''!=$_SESSION[CacheName . 'proorderid'])
      {
            /* 跳转到支付页 */
            header('location:../pay/paytype.php?orderid=' .$_COOKIE[CacheName . 'proorderid']);
        } else {

            showerr();
        }
    }
 function main() {
        $j = & $GLOBALS['j'];


        if ('y' == $j['success']) {
            $orderid = $j['orderid'];

            /* 跳转到支付页 */
            header('location:../pay/paytype.php?orderid=' . $orderid);
        } else {

            showerr();
        }
    }
}

$tp = new myclass();
unset($tp);
?>
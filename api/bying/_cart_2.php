<?php

/*扫购*/

/* 用户组接口 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . 'api/cls_template.php';

class myclassapi extends cls_api{
    function __construct() {
        $this->j =& $GLOBALS['j'];
        
        $this->act=$this->ract();
        
        switch ($this->act){
            case '':
                $this->main();
                break;
        }      
    }
  
   function main(){
        /*商品详情*/
        $detail['gic'] = '1'; //商品ic
        $detail['dic'] = '1'; //柜门ic
        $detail['title'] = '模拟商品名称'; //商品名称
        $detail['price'] = '2.5'; //价格
        $detail['preimg'] = '/_images/photos/pic_7.jpg'; //图片路径
        $detail['content'] = '商品描述商品描述商品描述商品描述商品品描描述品描述商品描述';//商品描述
        $detail['readme'] = '商品描述简介品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述';//商品简介
        $this->j['detail']= $detail;
               
        
        for ($i=0;$i<3;$i++)
        {
            /*商品列表*/
            $list[$i]['gic'] = '0';//商品编号
            $list[$i]['dic'] = '4'; //柜门ic
            $list[$i]['title'] = '模拟商品名称';
            $list[$i]['price'] = '2.1';
            $list[$i]['preimg'] = '/_images/photos/pic_6.jpg'; //图片路径
            $list[$i]['hasgood'] = '1'; //是否有货
            $list[$i]['mystatus'] = 'running'; //运行状态
            $list[$i]['doorstatus'] = 'close'; //门的状态

        }
        $this->j['list']= $list;
		
        $cart['allprice'] = '15.78';//订单总额
        $cart['counts'] = '20'; //商品数量
        $GLOBALS['j']['cart'] = $cart;



        if(1==$this->get('isprint')){
            print_r($this->j);
        }
    }
}

$myclassapi = new myclassapi();
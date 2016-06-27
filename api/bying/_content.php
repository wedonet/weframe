<?php

/*扫购*/

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'bying/_main.php';//扫购通用数据


class myapi extends cls_api {
    
    function __construct() {
        parent::__construct();
        
        $this->init();
        
       
        
        switch ($this->main->ract()) {
            default:
                $this->main();
                $this->output();
                break; 
        }
    }
  
    function init(){
         $doorid = $this->main->rqid('d');
         
         if($doorid<0){
             $doorid = $this->main->rqid('doorid');
         }
         
         $this->doorid = $doorid;
         
         /*提取门的信息*/
         $sql = 'select * from `'.sh.'_door` where 1 ';
         $sql .= ' and id=:doorid';
        
         $a_door = $this->pdo->fetchOne($sql, Array(':doorid'=>$doorid));
         
         if(false == $a_door){
             showerr('柜门号错误!');
         }
         
         $this->door =& $a_door;               
         $this->comid =& $a_order['comid'];
        
        $this->c_bying = new cls_bying();       
        $this->c_bying->getcompany($this->comid);
    }
   function main(){
        /*提取设备信息*/
        $sql = 'select * from `'.sh.'_device` where 1 ';
        $sql .= ' and id=:deviceid ';
        
        $result = $this->pdo->fetchOne($sql, Array(':deviceid'=>$this->door['deviceid']));     
        
        $this->j['device'] = $result;
        
    

        
        /*取当前柜门的商品信息*/
        $sql = 'select goods.title as goodstitle, goods.bigimg, goods.readme, goods.content ';
        $sql .= ' ,comgoods.price ';
        $sql .= ' from `'.sh.'_comgoods` as comgoods ';
        $sql .= ' left join `'.sh.'_goods` as goods on comgoods.goodsid=goods.id '; //从comgoods提价格
        $sql .= ' where 1 ';  
        $sql .= ' and comgoods.id=:comgoodsid';
 

        $result = $this->pdo->fetchOne($sql, Array(':comgoodsid'=>$this->door['comgoodsid']));
        if(false == $result){
            showerr('没找到这个商品信息！');
        }
        
        /*再加点其它信息进去*/
        $result['hasgoods'] = $this->door['hasgoods'];
        $result['doorid'] = $this->doorid;

        $GLOBALS['j']['detail'] = $result;      //当前商品信息
        
        
        
        /*原模拟数据*/
        /*
         *         $detail['gic'] = '1'; //商品ic
        $detail['dic'] = '1'; //柜门ic
        $detail['price'] = '2.5'; //价格
        $detail['bigimg'] = '/_images/photos/pic_7.jpg'; //图片路径
        $detail['content'] = '<!----------- 以下代码粘到后台商品内容编辑器中------------>

            <div class="titlen">产品参数</div><!---------------------------- 模块名称----------------------------->
            <div class="box">
                <h2>统一老坛酸菜牛肉面 (酸辣)</h2><!------------------------- 产品标题名称------------------------>
                <ul>
                    <li>面饼配料： 小麦粉、植物油、淀粉、食用盐等。</li><!--------------- 商品成分等信息（每个li写一个内容）------------->
                    <li>产品类型： 油炸方便面</li>
                    <li>含量： 120g桶装</li>
                    <li>保质期： 六个月</li>
                    <li>特殊说明： 本品配有调料包和叉子</li>
                </ul>
            </div>

            <div class="titlen">产品详情</div><!-------------------------- 模块名称----------------------------->
            <img src="../_images/content/laotan/2.jpg" title="商品照片"  /><!----------------------- 商品相关图片------------------>
            <img src="../_images/content/laotan/3.jpg" title="商品照片"  />
            <img src="../_images/content/laotan/5.jpg" title="商品照片"  />
            <img src="../_images/content/laotan/4.jpg" title="商品照片"  />
            <img src="../_images/content/laotan/6.jpg" title="商品照片"  />

            <!----------- 以上代码粘到后台商品内容编辑器中------------>';//商品描述
        $detail['readme'] = '商品描述简介品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述品描述';//商品简介
        $detail['goodstitle'] = '加多宝';//商品名称  
        $this->j['detail']= $detail;
         */

     

    }
}

$myclassapi = new myapi();
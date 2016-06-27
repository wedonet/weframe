<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once AdminApiPath . '_main.php'; //本模块数据

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
        $this->deviceid = $this->main->rid('deviceid');

        $c_company = new cls_companymain();

        /* 店铺信息添加进$globals['j']['company'] */
        $this->j['company'] = $c_company->getcompany($this->comid);


        $this->act = $this->main->ract();

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;
  

            case 'sel':
                $this->selgoods();
                $this->output();
                break;
               
            case 'dosel':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dosel();
                $this->output();
                break;            
            case 'del': //删除售卖品
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dodel();
                $this->output();
                break;   
        }
    }

    function mylist() {
        
        $sql = 'select door.*,goods.title as goodstitle, comgoods.price as price,goods.isgroup as isgroup from `'.sh.'_door` as door ';
        $sql .= ' left join `'.sh.'_comgoods` as comgoods on door.comgoodsid=comgoods.id '; //店铺售卖品和平台商品关联
        $sql .= ' left join `'.sh.'_goods` as goods on comgoods.goodsid=goods.id '; 
        $sql .= ' where 1  ';
        $sql .= ' and deviceid=:deviceid ';
        $sql .= ' order by id asc ';
        
        $a = $this->pdo->fetchAll($sql, Array(':deviceid'=>$this->deviceid));
        
        $this->j['list'] = $a;
    }


    
    function selgoods(){
        /*提取所有商品*/
        $sql = 'select comgoods.*,goods.title as title,goods.isgroup as isgroup ';
        $sql .= ' from `'.sh.'_comgoods` as comgoods ';
        $sql .= ' left join `'.sh.'_goods` as goods on comgoods.goodsid=goods.id ';
        $sql .= ' where 1 ';
        $sql .= ' and comgoods.comid=:comid ';
        $sql .= ' and comgoods.price>0 ';
        $sql .= ' order by id asc';
        
        $this->j['list'] = $this->pdo->fetchAll($sql, Array(':comid'=>$this->comid));
    }

    function dosel(){
        $we =& $GLOBALS['main'];
        
        $doorid = $we->rqid('doorid');
        $comgoodsid = $we->rqid('comgoodsid');
        
        /*提取店铺售卖的这个商品信息*/
        $sql = 'select * from `'.sh.'_comgoods` where 1';
        $sql .= ' and id=:comgoodsid';
        $a_comgoods = $this->pdo->fetchOne($sql, Array(':comgoodsid'=>$comgoodsid));
        
        if(false == $a_comgoods){
            $this->ckerr('没找到这件商品');
        }
        
        /*提取门的信息*/
        $sql = 'select * from `'.sh.'_door` where 1 ';
        $sql .= ' and id=:doorid ';
        $a_door = $this->pdo->fetchOne($sql, Array(':doorid'=>$doorid));
        
        if(false == $a_door){
            $this->ckerr('没找到这个门');
        }   
        
        /*检测是不是本店铺售卖的商品*/
        
        /*检测是否有货状态，有货时不能设置售卖品，否则会引起货不价和内容*/
        if( 1 == $a_door['hasgoods'] ){
            $this->ckerr('有货时不能更改售卖品');
        }
        
        
        /*检测此门是不是已经卖这种商品了*/
        if( $a_door['comgoodsid'] ==$comgoodsid  ){
            $this->ckerr('这个门已经在卖这件商品了,请不要重复操作!');
        }
        
        
        /*更新这个门的售卖品*/
        $rs['goodsid'] = $a_comgoods['goodsid'];
        $rs['goodsic'] = $a_comgoods['goodsic'];
        $rs['comgoodsid'] = $a_comgoods['id'];
        
  
        $rs['comgoodsid'] = $comgoodsid;
        
        $this->pdo->update(sh.'_door', $rs, 'id=:doorid', Array(':doorid'=>$doorid));
        
        $this->j['success'] = 'y';
    }
    
    
    function dodel(){
        $we =& $GLOBALS['main'];
        
        $doorid = $we->rqid('doorid');

        
        /*提取门的信息*/
        $sql = 'select * from `'.sh.'_door` where 1 ';
        $sql .= ' and id=:doorid ';
        $a_door = $this->pdo->fetchOne($sql, Array(':doorid'=>$doorid));
        
        if(false == $a_door){
            $this->ckerr('没找到这个门');
        }   
        

   
        
        /*更新这个门的售卖品*/
        $rs['goodsid'] = 0;
        $rs['goodsic'] = '';
        $rs['comgoodsid'] = 0;
        
        $rs['hasgoods'] = 0;
  
        
        $this->pdo->update(sh.'_door', $rs, 'id=:doorid', Array(':doorid'=>$doorid));
        
        
        /*更新这个设备的剩余商品量， 更新柜门机数量*/ 
		$sql = 'update `'.sh.'_device` set goodsnum=(Select count(*) from `'.sh.'_door` where 1 and hasgoods=1 and deviceid=:deviceid)  where 1 '; 
		$sql .= 'and id=:deviceid ';
		$this->pdo->doSql($sql, Array(':deviceid'=>$a_door['deviceid']));	
        
        
        
        
        $this->j['success'] = 'y';      
        
    }
    

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
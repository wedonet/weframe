<?php

/* 扫购 */

require_once( __DIR__ . '/../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'bying/_main.php'; //扫购通用数据

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->init();
        switch ($this->act) {
            default:
                $this->main();
                $this->output();
                break;
        }
    }

function init() {

        $doorid = $this->main->rqid('d');

        if ($doorid < 0) {
            $doorid = $this->main->rqid('doorid');
        }
        
        /*如果没有doorid then从cookie提，if连cookie里都没有 那么只能提示重新扫码了
         *  else存进cookie里一个d 代表扫进来的doorid*/
        if($doorid<0){

            if( isset($_COOKIE[CacheName.'_d'])){
                //die('a');
                $doorid = $_COOKIE[CacheName.'_d'];
                
                if (!is_numeric($doorid)) {
                    $doorid = -1;
                }else{
                    $doorid = $doorid * 1;
                }
            }else{
                //die('b');
                $doorid = -1;
            }        
        }
        
        /*if doorid>0 then 存进cookie, else提示请重新扫码*/
        if($doorid>0){
            
            setcookie(CacheName . '_d', $doorid, time() + 3600 * 24 * 30);    
				//	ob_get_clean();
            //echo 'cookie了';
        }else{
            showerr('请重新扫码进入!');
        }


        $this->doorid = $doorid;

        /* 提取门的信息 */
        $sql = 'select * from `' . sh . '_door` where 1 ';
        $sql .= ' and id=:doorid';

        $a_door = $this->pdo->fetchOne($sql, Array(':doorid' => $doorid));
        $this->a_door = & $a_door;

        if (false == $a_door) {
            showerr('柜门号错误!');
        }

        $this->door = & $a_door;
        $this->comid = & $a_door['comid'];
        $this->deviceid = & $a_door['deviceid'];//门属于哪个设备
       
        $this->c_bying = new cls_bying();
        $this->c_bying->getcompany($this->comid);
    }

    function main() {

        /* 取格子+商品信息 */
        $sql = 'select door.*,goods.title as goodstitle,goods.preimg ';
        $sql .= ' ,comgoods.price ';
        $sql .= ' ,door.hasgoods,door.id as doorid ';
        $sql .= ' from `' . sh . '_door` as door ';
        $sql .= ' left join `' . sh . '_goods` as goods on door.goodsid=goods.id '; //从goods提图片和描述
        $sql .= ' left join `' . sh . '_comgoods` as comgoods on door.comgoodsid=comgoods.id '; //从comgoods提价格
        $sql .= ' where 1 ';

        $sql .= ' and door.deviceid=:deviceid';//取柜门表的deviceid
        $para[':deviceid'] = $this->deviceid;

        $sql .= ' order by door.id asc ';
        
        $result = $this->pdo->fetchAll($sql,$para);
        $GLOBALS['j']['list'] = $result;
 
    }   

}

$myclassapi = new myapi();

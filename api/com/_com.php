<?php

/* 铺位 */

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'bying/_main.php'; //扫购通用数据

class myapi extends cls_api {

	function __construct() {
		parent::__construct();


		switch ($this->main->ract()) {
			case 'gofirstdoorid':
				$this->gofirstdoorid();
				$this->output();
				break;
			default:
				$this->main();
				$this->output();
				break;
		}
	}


	function main() {
		$comid = $this->main->rqid('id');
		
		$para['comid']= $comid;
		
		/*设备列表*/
		$sql = 'select place.*,device.id as deviceid from `'.sh.'_device` as device ';
		$sql .= ' left join `'.sh.'_place` as place on device.placeid=place.id ';
		$sql .= ' where 1 ';
		$sql .= ' and device.comid=:comid ';
		$sql .= ' order by id asc ';
		
		$result = $this->pdo->fetchAll($sql, $para);
		
		$this->j['list']=$result;
	}

	function gofirstdoorid(){
		$deviceid = $this->main->rqid('deviceid');
		
		$sql = 'select id from `'.sh.'_door` where 1 ';
		$sql .= ' and deviceid=:deviceid';
		$sql .= ' order by id asc ';
		$sql .= ' limit 1 ';
		
		$para['deviceid'] = $deviceid;
		
		$result = $this->pdo->fetchOne($sql, $para);
		
		$this->j['firstdoorid'] = $result['id'];		

	}

}

$myclassapi = new myapi();

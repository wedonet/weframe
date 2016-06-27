<?php

/* 店铺 */

require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'bying/_main.php'; //扫购通用数据

class myapi extends cls_api {

	function __construct() {
		parent::__construct();


		switch ($this->main->ract()) {
			default:
				$this->main();
				$this->output();
				break;
		}
	}


	function main() {
		$sql = 'select * from `'.sh.'_com` order by id desc ';
		
		$result = $this->pdo->fetchAll($sql);
		
		$this->j['list']=$result;
	}

	

}

$myclassapi = new myapi();

<?php

/* 补货首页 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'replenishment/_main.php'; //补货页通用数据

//require_once 'power.php'; //补货页通用数据

/* 返回用户组 */

class myapi extends cls_api {

	function __construct() {
	parent::__construct();

       
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
		switch ($this->main->ract()) {
			case '':
				$this->main();
				$this->output();
				break;
		}
	}

	/**/

	function main() {
		$comid = $this->main->user['comid'];


		$sql = 'select goodsid ,count(*) as counts ';
		$sql .= ' ,goods.preimg ';
		$sql .= ' ,goods.title ';
		$sql .= ' from `' . sh . '_door` as door ';
		$sql .= ' left join `' . sh . '_goods` as goods on door.goodsid=goods.id ';

		$sql .= ' left join `' . sh . '_device` as device on door.deviceid=device.id ';

		$sql .= ' where 1 ';
		$sql .= ' and door.hasgoods=0 ';
		$sql .= ' and door.comid=' . $comid;

		$sql .= ' and door.goodsid>0'; //只统计有货的

		$sql .= ' and device.isrun=1'; //只统计运行的设备

		$sql .= ' group by door.comgoodsid ';

		$result = $this->pdo->fetchAll($sql);


		/* 商品信息 */
//        for($i=0;$i<10;$i++){
//            $a[$i]['id'] = $i;
//            $a[$i]['title'] = '名称';
//            $a[$i]['preimg'] = '/_images/photos/pre/pic_7.jpg';
//            $a[$i]['counts'] = $i; //需要补货的数量
//        }

		$GLOBALS['j']['list'] = $result;
	}

}

$myapi = new myapi();
unset($myapi);
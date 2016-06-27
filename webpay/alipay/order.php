<?php
require_once ('../../global.php');
require_once syspath . '_inc/cls_biz.php'; //神灯业务处理
/*$tradeid 支付的交易号*/
/*$allprice 支付总价*/ 


function updateorder($orderid, $tradeid, $allprice){
	$pdo =& $GLOBALS['pdo'];

	
	try {
		$pdo->begintrans();

		$c_biz = new cls_biz();

		$c_biz->updateorder(
			$orderid, 
			$tradeid,
			$allprice, 
			'alipay',
			true);

		unset($c_biz);


		$pdo->submittrans();	

	}catch (PDOException $e) {
		$pdo->rollbacktrans();
		echo ($e);
		die();
	}
}

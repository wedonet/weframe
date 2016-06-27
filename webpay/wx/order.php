<?php


require_once (__DIR__ . '/../../global.php');

require_once "../lib/WxPay.Api.php";
require_once '../lib/WxPay.Notify.php';
//require_once 'log.php';

require_once syspath . '_inc/cls_biz.php'; //神灯业务处理

//Log::DEBUG("call back:" . json_encode($data));

//updateorder(14035,'a123_14035',1);

//print_r($GLOBALS['errmsg']);


//outputxml();




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
			'wx',
			true);

		unset($c_biz);


		$pdo->submittrans();	

	}catch (PDOException $e) {
		$pdo->rollbacktrans();
		echo ($e);
		die();
	}
}







function outputxml(){
	header('Content-type: text/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	//echo "<users><user><name>小小菜鸟</name><age>24</age><sex>男</sex></user><user><name>艳艳</name><age>23</age><sex>女</sex></user></users>";
	?><xml>
		<return_code><![CDATA[SUCCESS]]></return_code>
		<return_msg><![CDATA[OK]]></return_msg>
	</xml>
	<?php
}
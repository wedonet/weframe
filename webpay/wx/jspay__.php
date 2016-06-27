<?php 

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once (__DIR__ . '/../../webpay/global.php');


require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';




//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

/*get order info*/
$orderid = $_GET['orderid'];
$a_order = getorder($orderid);

/*检测还有没有商品*/

if( !hasgoods($a_order['doorids'])) {
	//Log::DEBUG("call back:" . $orderid.'已经支付过了');
	die('这个柜门已经没货了');
}





//①、获取用户openid
$tools = new JsApiPay();
$openId = $tools->GetOpenid();

//②、统一下单
$input = new WxPayUnifiedOrder();




$input->SetBody('神灯扫购');
$input->SetAttach("test");

$input->SetOut_trade_no($orderid);

$input->SetTotal_fee($a_order['allprice']);

$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
$input->SetNotify_url("http://www.ejiayuding.com/webpay/wx/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);


//print_r($order);die;

if('FAIL' != $order['return_code']){
	if( 'FAIL' == $order['result_code'] ){
		//echo '<a href="javascript:history.back(-1);">返回上一页</a>';
		
		die('参数错误'.$order['err_code_des']);
	}
}else{
	die('定单信息错误'.$order['return_msg']);
}
//echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';

$jsApiParameters = $tools->GetJsApiParameters($order);



//获取共享收货地址js函数参数
//$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 

	<script src="/_js/jquery-1.11.3.min.js?t=1"></script>  

    <title>微信支付</title>
    <script type="text/javascript">

		window.onload = function(){
			callpay();
		}

	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg.'');
				//alert(res.err_code+res.err_desc+res.err_msg);

				//alert(res.err_msg);

				if(res.err_msg == "get_brand_wcpay_request:ok" ) {

					/*有可能支付成功，但已经没货了（由于两人已经同时进入微信进行支付了）*/

					/*跟据数据库的提示做，检测定单状态，如果没支付then提取原因*/

					//alert('支付成功\n<?php echo $a_order["doorids"] ?>柜门已打开，\n请取走您的货品!');


					//window.location.href='<?php echo $GLOBALS["config"]["weburl"] ?>/bying/index.php?d=<?php echo $a_order["doorids"]?>';


					//alert('支付成功\n<?php echo $a_order["doorids"] ?>柜门已打开，\n请取走您的货品!');


					//支付成功，转入取货页


					/*这通知成功了就转入取货页*/
					window.location.href='<?php echo $GLOBALS["config"]["weburl"] ?>/bying/take.php?orderid=<?php echo $a_order["id"]?>';
				}else{
					alert('支付失败');
					/*失败*/
				} 
				
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>

</head>
<body>
    <br/>
    <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px"><?php echo ($a_order['allprice']/100).'元'?></span></b></font><br/><br/>
	<div>正在打开微信支付</div>
	<div align="center" style="display:none">
		<button onclick="callpay()" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button">立即支付</button>
	</div>
</body>
</html>


<?php

function getorder($orderid){
	$we =& $GLOBALS['we'];
	$pdo =& $GLOBALS['pdo'];

	$sql = 'select * from `'.sh.'_order` where 1';
	$sql .= ' and id=:id';

	

	$result = $pdo->fetchOne($sql, Array(':id'=>$orderid));

	//$doorid = $result['doorid'];

	return $result;
}


/*检测还有没有商品*/
function hasgoods($doorid){
	$pdo =& $GLOBALS['pdo'];

	$sql = 'select count(*) from `'.sh.'_door` where 1 ';
	$sql .= ' and hasgoods=1';
	$sql .= ' and id=:doorid';

	$counts = $pdo->counts($sql, Array('doorid'=>$doorid));

	if( 0 == $counts){
		return false;
	}else{
		return true;
	}
}
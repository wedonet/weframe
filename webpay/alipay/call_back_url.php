<?php
/*
 * 功能：支付宝页面跳转同步通知页面 版本：3.3 日期：2012-07-23 说明： 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。 ************************页面功能说明************************* 该页面可在本机电脑测试 可放入HTML等美化页面的代码、商户业务逻辑程序代码 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 */
require ('../../global.php');
require_once ("alipay.config.php");
require_once ("lib/alipay_notify.class.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <script src="/_js/jquery-1.11.3/jquery.min.js"></script>		
		<title>支付宝即时到账交易接口</title>
                
	</head>
	<body>
            
            <?php
		/* 计算得出通知验证结果 */
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
              
		if ($verify_result) { // 验证成功
			// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// 请在这里加上商户的业务逻辑程序代码
			// ——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			// 获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
			// 商户订单号
			$out_trade_no = $_GET ['out_trade_no'];
			$give = explode('_', $out_trade_no);

			echo '<div style="font-size:18px;text-align:center;">支付成功,转向提货页<a href="'.webdir.'bying/take.php?d='.$give[2].'&clear=clear&orderid='.$give[1].'">...</a></div>';
			?>
			
			<?php
			
		} else {
			// 验证失败
			// 如要调试，请看alipay_notify.php页面的verifyReturn函数
			echo "验证失败";
		}
		?>
            
            <script type="text/javascript">
                $(document).ready(function() {
                   var href ="<?php echo webdir.'bying/take.php?d='.$give[2].'&clear=clear&orderid='.$give[1] ?>";//支付成功后的提醒，不再让用户自己点击操作了，2秒后自动跳转到首页
                            setTimeout(window.location.href = href , 1000);
                })
                </script>
	</body>
</html>
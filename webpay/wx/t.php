<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {

        require_once(ApiPath . 'pay/paytype.php'); //访问接口去

        $this->act = $this->ract();

        switch ($this->act) {
			case 'pay': //生成定单，跳转到微信支付				
				break;
            default:
                $this->main(); //主内容区
                break;
        }
    }
	
	


	
	function main(){
		$j =& $GLOBALS['j'];

		$comgoodsid = $this->rqid('comgoodsid');
		$doorid = $this->rqid('doorid');

		?><!DOCTYPE html>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
				<meta name="viewport" content="width=device-width, initial-scale=1"/>


				<!--如下是手机端打开时的处理-->
				<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
				<meta name="apple-mobile-web-app-capable" content="yes" />    
				<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
				<meta name="format-detection" content="telephone=yes"/>
				<meta name="msapplication-tap-highlight" content="no" />


				<link rel="stylesheet" href="/_css/base.css" type="text/css"/>
				<link rel="stylesheet" href="/_css/main.css" type="text/css"/>
				<link rel="stylesheet" href="/_css/main-type.css" type="text/css"/>
				<link rel="stylesheet" href="/_css/plus.css" type="text/css"/>


				<script src="/_js/jquery-1.11.3/jquery.min.js"></script>
				<script src="/_js/main.js"></script>
			

			</head>
				 
			<body>
			   <div class="title">支付方式</div>
			   <a class="titlebg" href="javascript:void(0);" onclick="javascript :history.go(-1);"></a>
				<div class="main">
					<div class="checked clearfix">
					   <div class="rental">支付总额：<span class="red"><?php echo $j['allprice'] ?></span> 元</div>
					   <div class="paytitle">请选择支付方式</div>
					   <div class="paytype">
							<div class="paylist">
								<ul>
									<li><a href="?act=pay&amp;paytype=wx&amp;comgoodsid=<?php echo $comgoodsid ?>&amp;doorid=<?php echo $doorid ?>" class="pay111">&nbsp;微信支付</a></li>
									<li><a href="javascript:void(0)" class="pay222">&nbsp;支付宝支付（稍后开放）</li>
								</ul>
							</div>
					   </div>
					   <div class="clearfix"></div>
					</div>

				</div>
			</body>
		</html>


	<?php
	} 
}

$tp = new myclass();
unset($tp);

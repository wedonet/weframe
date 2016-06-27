   
   // //首页加入购物车 
//    $(function(removecar){
//            var tmp ;
//            $('.bnt').bind('click',function(){
//                    if(tmp) tmp.remove(); 
//                    var box=$(this).parent();
//                    var img=box.find(".mainpic");
//                    tmp=img.clone();
//                    var p=$(".mainpic").offset();
//                    var p2=$(car).offset();
//                    tmp.addClass('_box').css(p).appendTo(box  );
//                    p2=$.extend(p2,{height:20,width:20,opacity:10});
//                    $(tmp).animate(p2, "slow",function(){
//                    tmp.remove();
// 					$(".ccnum").html(parseInt($(".ccnum").html())+1);
//					$('.addcart').addClass('dis-click')
//
//                      });
//            });
//    });
	
	//title的返回；
        function go()
        {
        window.history.go(-1);
        }
		
	//微信的支付调用
//		wx.config({
//			appId: 'wx71203c969b52d583',
//			timestamp: '1448269675',
//			nonceStr: 'xEbHqGAvNvlvPEMF',
//			signature: '10869b7732ad7d64ce4290c6f0cbefbe526ddacd',
//			jsApiList: [
//				'chooseWXPay'
//			]
//		});
//			wx.ready(function () {
//				wx.chooseWXPay({
//					timestamp: 0, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
//					nonceStr: '', // 支付签名随机串，不长于 32 位
//					package: '', // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
//					signType: '', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
//					paySign: '', // 支付签名
//					success: function (res) {
//						// 支付成功后的回调函数
//					}
//				});
//			});
			
			

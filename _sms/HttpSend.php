<?php
class HttpSend {
	function getSend($url, $param) {
		$ch = curl_init ( $url . "?" . $param );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_BINARYTRANSFER, true );
		
		$output = curl_exec ( $ch );
		
		return $output;
	}
	function postSend($url, $param) {
		if(!function_exists('curl_init'))return false;
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $param );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		return $data;
	}
	function gbkToUtf8($str) {
		return rawurlencode ( iconv ( 'GBK', 'UTF-8', $str ) );
		//return rawurlencode ($str);
	}
	
	function smshttpSend($Phone='15522000677',$Content='您注册e家人的激活码为：xxxxxx，请妥善保管，详询：400-xxx-xxxx 【e家预定】') {
		// 以下为所需参数,测试时请修改,中文参数请转码
		$strReg = "101100-WEB-HUAX-318472"; // 注册号(由华兴软通提供)
		$strPwd = "GYAWMMLX"; // 密码(由华兴软通提供)
		$strSourceAdd = ""; // 子通道号，可为空（预留参数一般为空）
		$strTim = $this->gbkToUtf8 ( "2012-2-17 15:00:00" ); // 定时发送时间,时间格式yyyy-MM-dd HH:mm:ss,含有空格请转码
		//$strPhone = "13391750223,18701657767"; // 手机号码，多个手机号用半角逗号分开，最多1000个
		//$strContent =$this->gbkToUtf8 ( "h!@#$%^&*(){}[];ttpPH再来一冷色P华测P" ); // 短信内容
		
		$strPhone = $Phone; // 手机号码，多个手机号用半角逗号分开，最多1000个
		$strContent =$Content; // 短信内容
		
		$strUname = $this->gbkToUtf8 ( "华测" ); // 用户名，不可为空
		$strMobile = "13391750000"; // 手机号，不可为空
		$strRegPhone = "01065685318"; // 座机，不可为空
		$strFax = "01065685318"; // 传真，不可为空
		$strEmail = "hxrt@stongnet.com"; // 电子邮件，不可为空
		$strPostcode = "100080"; // 邮编，不可为空
		$strCompany = $this->gbkToUtf8 ( "通软兴华" ); // 公司名称，不可为空
		$strAddress = $this->gbkToUtf8 ( "地阳ja" ); // 公司地址，不可为空
		
		$strNewPwd = "AAAAAAAA"; // 修改后的密码
		                         
		// 以下参数为服务器URL,以及发到服务器的参数，不用修改
		$strRegUrl = "http://www.stongnet.com/sdkhttp/reg.aspx";
		$strBalanceUrl = "http://www.stongnet.com/sdkhttp/getbalance.aspx";
		$strSmsUrl = "http://www.stongnet.com/sdkhttp/sendsms.aspx";
		$strSchSmsUrl = "http://www.stongnet.com/sdkhttp/sendschsms.aspx";
		$strStatusUrl = "http://www.stongnet.com/sdkhttp/getmtreport.aspx";
		$strUpPwdUrl = "http://www.stongnet.com/sdkhttp/uptpwd.aspx";
		
		$strRegParam = "reg=" . $strReg . "&pwd=" . $strPwd . "&uname=" . $strUname . "&mobile=" . $strMobile . "&phone=" . $strRegPhone . "&fax=" . $strFax . "&email=" . $strEmail . "&postcode=" . $strPostcode . "&company=" . $strCompany . "&address=" . $strAddress;
		$strBalanceParam = "reg=" . $strReg . "&pwd=" . $strPwd;
		$strSmsParam = "reg=" . $strReg . "&pwd=" . $strPwd . "&sourceadd=" . $strSourceAdd . "&phone=" . $strPhone . "&content=" . $strContent;
		$strSchSmsParam = "reg=" . $strReg . "&pwd=" . $strPwd . "&sourceadd=" . $strSourceAdd . "&tim=" . $strTim . "&phone=" . $strPhone . "&content=" . $strContent;
		$strStatusParam = "reg=" . $strReg . "&pwd=" . $strPwd;
		$strUpPwdParam = "reg=" . $strReg . "&pwd=" . $strPwd . "&newpwd=" . $strNewPwd;
		
		$strRes = "";
		// 以下为HTTP接口主要方法，测试时请打开对应注释进行测试
		// 注册
		// $strRes = $sender->postSend($strRegUrl,$strRegParam);
		// 查询余额
		// $strRes = $sender->postSend($strBalanceUrl,$strBalanceParam);
		// 发送短信
		$strRes = $this->postSend ( $strSmsUrl, $strSmsParam );
		//$this->postSend ( $strSmsUrl, $strSmsParam );
		// 定时短信
		// $strRes = $sender->postSend($strSchSmsUrl,$strSchSmsParam);
		// 状态报告
		// $strRes = $sender->postSend($strStatusUrl,$strStatusParam);
		// 修改密码
		// $strRes = $sender->postSend($strUpPwdUrl,$strUpPwdParam);
		if($strRes==false)return false;
	    $strarr=explode('&',$strRes);
	    $strarr['sendstate']=explode('=',$strarr[0])[1];
	    $strarr['sendmsgstate']=explode('=',$strarr[1])[1];
	    return  $strarr;
		//echo $strRes;
	}
}
?>
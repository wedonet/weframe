<?php
/** 
 * 云片网短信发送接口
 * @author zsh
 * 
 */
class yunpiansms {
	
	// TODO - Insert your code here
	var $apikey='0a514aacaad683b6c713c825bea595b6';
	var $mobile='15522000677';
	var $text='';
	
	/**
	 */
	function __construct() {
		
		// TODO - Insert your code here
	}
	/**
	 */
	function getsendres($mobile,$text=false) {
	// TODO - Insert your code here
	if($text){
	//stop($text);
		$jsondata=$this->send_sms($this->apikey,$text,$mobile);
		$jsondata=json_decode($jsondata,true);
		$jsondata['sendstate']=$jsondata['code'];
		$jsondata['sendmsgstate']=$jsondata['msg'];
		return $jsondata;
	}
	return false;
	}
	//模板接口样例（不推荐。需要测试请将注释去掉。)
	/* 以下代码块已被注释
	 $apikey = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"; //请用自己的apikey代替
	$mobile = "xxxxxxxxxxx"; //请用自己的手机号代替
	$tpl_id = 1; //对应默认模板 【#company#】您的验证码是#code#
	$tpl_value = "#company#=云片网&#code#=1234";
	echo tpl_send_sms($apikey,$tpl_id, $tpl_value, $mobile);
	*/
	
	
	/**
	 * 通用接口发短信
	 * apikey 为云片分配的apikey
	 * text 为短信内容
	 * mobile 为接受短信的手机号
	 */
	function send_sms($apikey, $text, $mobile){
		$url="http://yunpian.com/v1/sms/send.json";
		//$text=iconv("GBK","UTF-8",$text);
		$encoded_text = urlencode("$text");
		$post_string="apikey=$apikey&text=$encoded_text&mobile=$mobile";
		return $this->sock_post($url, $post_string);
	}
	
	/**
	 * 模板接口发短信
	 * apikey 为云片分配的apikey
	 * tpl_id 为模板id
	 * tpl_value 为模板值
	 * mobile 为接受短信的手机号
	 */
	function tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile){
		$url="http://yunpian.com/v1/sms/tpl_send.json";
		$encoded_tpl_value = urlencode("$tpl_value");  //tpl_value需整体转义
		$post_string="apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
		return $this->sock_post($url, $post_string);
	}
	
	/**
	 * url 为服务的url地址
	 * query 为请求串
	 */
	function sock_post($url,$query){
		$data = "";
		$info=parse_url($url);
		$fp=fsockopen($info["host"],80,$errno,$errstr,30);
		if(!$fp){
			return $data;
		}
		$head="POST ".$info['path']." HTTP/1.0\r\n";
		$head.="Host: ".$info['host']."\r\n";
		$head.="Referer: http://".$info['host'].$info['path']."\r\n";
		//	$head.='<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
		$head.="Content-type: application/x-www-form-urlencoded;charset=UTF-8\r\n";
		$head.="Content-Length: ".strlen(trim($query))."\r\n";
		$head.="\r\n";
		$head.=trim($query);
		$write=fputs($fp,$head);
		$header = "";
		while ($str = trim(fgets($fp,4096))) {
			$header.=$str;
		}
		while (!feof($fp)) {
			$data .= fgets($fp,4096);
		}
		return $data;
	}
	/**
	 */
	function __destruct() {
		
		// TODO - Insert your code here
	}
}
?>
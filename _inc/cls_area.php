<?php

/**
* Author by Xu Zhongjian
* 2015.5.25
* 城市相关方法
*/
class Cls_Area
{
	//获取城市列表
	function getcitylist($citymark='')
	{
		$sql = 'select * from '.sheet.'_area where mytype="city" and isuse=1';
		if($citymark!=''){
			$sql .=' and citymark like "'.$citymark.'"';
		}
		//$sql .=' group by citymark';
		//stop($sql);
		$rs = $GLOBALS['we']->execute($sql);
		if($rs==false){
			return array();
		}
		return $rs;
	}
}
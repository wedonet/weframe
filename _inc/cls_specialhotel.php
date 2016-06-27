<?php

/**
 * Short description.
 * @package     main
 * @subpackage  classes
 * @abstract    Classes defined as abstract may not be instantiated
 */
class cls_specialhotel {
	function __construct() {
		$this->we = &$GLOBALS['we'];
		
		$paytype['hotel'] = '到店支付';
        $paytype['online'] = '在线支付';
		
		$this->paytype = & $paytype;
	}
	
	/*特价房存定单
	* 成功返回定单idlist,失败返回空;
	*/
	function SaveOrder($a_user, $bywho='self',$url=null) {
		loadc('c_hotel', 'cls_hotel');
		$this->c_hotel =& $GLOBALS['c_hotel']; 

		loadc('c_hotelorder', 'cls_hotelorder');
		$this->c_hotelorder =& $GLOBALS['c_hotelorder'];

		loadc('c_shotel', 'cls_specialhotel');
		$this->c_shotel =& $GLOBALS['c_shotel'];

		/* ============================================================================
		 * 定义 */
		$orderroomcount = 1; //定了几间房		
		$roomdate = array();
		$a_roomcode = array(); //待绑定的房号

		$todaytimeint = strtotime(date('Y-m-d', time())); //今天转为整型

		$orderid = ''; /* 生成的定单号, 有可能生成很多定单号,格式为字符串 */


		/* =============================================================================
		 * 接收参数 */

		$a_order = $this->getrequestoforder(); //接收定单信息, 这些信息入库
		
		/* 有错误信息就返回 */
		if ( haserr() ) {
			return '';
		}

		/* 预定总房间数, 不入库 */
		$orderroomcount = 1;


		/* =============================================================================
		 *  处理数据, 时间变为unix格式 
		 */
		$a_order['mydate1'] = strtotime($a_order['mydate1']);
		$a_order['mydate2'] = strtotime($a_order['mydate2']);



		/* 取得酒店和房型信息 */
		$sql = $this->c_hotel->gethotel($a_order['hotelid']);
		$a_hotel = $this->we->exeone($sql);
		if (false == $a_hotel) {
			werr('没找到酒店信息');
			return '';
		}

		/* 跟据房型id, 提取房型信息 */
		$sql = $this->c_hotel->getroom($a_order['roomid']); 
		$a_room = $this->we->exeone($sql);
		if (false == $a_room) {
			werr('没找到房间信息');
			return '';
		}

		$guestinfo = $this->getguestinfo($orderroomcount, $a_room['r_person'], $a_order['hotelid'],$a_order); //取得入住人信息数组; r_person = 这间房能住几个人
		if($guestinfo==false)return '';
		if(false==$this->c_hotelorder->checkcodelistbyguestinfo($guestinfo,$a_order)){
			return '';
		}

		/* =============================================================================
		 * 检测房间状态和酒店状态
		 */
		if (0 == $a_room['isuse']) {
			$GLOBALS['errmsg'].='这个房间已经关闭, 请重新选择房间';
			return '';
		}

		if (0 == $a_hotel['isopen']) {
			$GLOBALS['errmsg'].='这个酒店已经被关闭, 请重新选择酒店!';
			return '';
		}

		/* 检测入住时间段不能大于1天 */
		if (($a_order['mydate1'] - $a_order['mydate2']) / 86400 > 10) {
			werr('入住时间段不能超过1天');
			return '';
		}

		/* 检测不能定今天以前的 */

		if ($a_order['mydate1'] < $todaytimeint) {
			//stop( $a_order['mydate1'].'/'. $todaytimeint );            
			werr('预定时间不能小于今天!');
			return '';
		}

		/* 检测入住时间，不能大于离店时间 */
		if ($a_order['mydate1'] > $a_order['mydate2']) {
			werr('入住时间不能大于离店时间');
			return '';
		}
		
		/* =============================================================================
		 * 提取这些天的房价, 存进roomdate 
		 */
		$sql = 'select id, mydate, price, roomsupply, roomorder, roomremain  from `' . sheet . '_specialroomlist` where 1 ';
		$sql .= ' and hotelid=' . $a_order['hotelid'];
		$sql .= ' and roomid=' . $a_order['roomid'];
		$sql .= ' and isopen=1';
		$sql .= ' and mydate >= ' . $a_order['mydate1'] . ' and mydate<' . $a_order['mydate2']; /* 在这个时间段内 */

		$roomlist = $this->we->execute($sql);

		if (FALSE == $roomlist) {
			$GLOBALS['errmsg'].='没找到房间列表';
			return '';
		}

		//检查这个时间段有没有房间
		if (0 == $roomlist['total']) {
			$GLOBALS['errmsg'].='这个时间段没有房间';
			return '';
		}

		//	按日期重排一下, 以日期为key
		foreach ($roomlist['rs'] as $v) {
			$roomdate[$v['mydate']] = $v;
		}


		/* ======================================================================
		 * 循环时间, 计算这些定单总价格
		 * 	总价回0
		 *  */
		$a_order['allprice'] = 0;
		$errmsg = '';
		for ($i = $a_order['mydate1']; $i < ($a_order['mydate2']); $i+=86400) {

			if (!array_key_exists($i, $roomdate)) {
				$errmsg .= '<li>' . date('Y-m-d', $i) . '没有房间</li>';
			} else {
				//检测剩余房量
				//剩余房量<定购量
				if ($roomdate[$i]['roomremain'] < $orderroomcount) {
					$errmsg .= '<li>' . date('Y-m-d', $i) . $a_room['title'] . '还有 ' . $roomdate[$i]['roomremain'] . ' 间空房,请重新选择房间数量</li>';
				} else {
					$a_order['allprice'] += (($a_order['roomcount'] * $roomdate[$i]['price']));

					$thisprice['mydate'] = $i;
					$thisprice['price'] = $roomdate[$i]['price'];

					$everyprice[] = $thisprice;
				}
			}
		}
		//如果房量不足做提示
		if (strlen($errmsg) > 0) {
			$GLOBALS['errmsg'].= $errmsg;
			return '';
		}



		/* ===================================================================
		 * 检测房间是否存在,同时找出酒店id, 检测, 并存入
		 */

		$a_order['hotelid'] = $a_hotel['id'];
		$a_order['hotelname'] = $a_hotel['title'];
		$a_order['roomname'] = $a_room['title'];

		//到店确认时间
		$a_order['arrivetime1'] = $a_hotel['arrivetime1'];
		$a_order['arrivetime2'] = $a_hotel['arrivetime2'];



		/* 保存用户信息 */
		$a_order['uid'] = $a_user['id'];
		$a_order['unick'] = $a_user['u_nick'];
		$a_order['stimeint'] = time();
		$a_order['stime'] = date('Y-m-d H:i:s', $a_order['stimeint']);

		/* 最后一次修改时间 */
		$a_order['euid'] = $a_user['id'];
		$a_order['eunick'] = $a_user['u_nick'];
		$a_order['etimeint'] = time();
		$a_order['etime'] = date('Y-m-d H:i:s', $a_order['etimeint']);



		/* 保存定单状态 */
		/* 下单后直接生效 */
		$a_order['mystatus'] = 'payed';
		$a_order['mystatusname'] = $this->c_hotel->orderstatus[$a_order['mystatus']];
		/* stop(serialize($everyprice)); */
		/* 存每天的房价 */
		$a_order['everyprice'] = serialize($everyprice);

		$a_order['bywho'] = $bywho;


		/* =======================================================================
		 * 计算每个定单的总价，和记录每天的房价
		 * 每个定单的价格是一样的
		 * 分别存进allprice,pricelist字段
		 * price格式： 日期|价格@下一天的日期|价格
		 * 计算方法，循环累加每一天的房价
		 */
		/*$allprice = 0;
		$aprice = array();
		foreach ($roomdate as $v) {
			$allprice += $v['price'];
			$aprice[] = $v['mydate'] . '|' . $v['price'];
		}*/
		/* $pricelist = implode('@', $aprice);
		  $a_order['allprice'] = $allprice;
		  $a_order['pricelist'] = $pricelist; */

		/*特价房定单判断标识*/
		$a_order['isspecial'] = 1;
		$a_order['ispayed']=1;
		$a_order['paytypename'] = $this->paytypename($a_order['paytype']);
		
		/* =======================================================================
		 * 保存定单,生成定单id 
		 * $id = $we->pdo->insert(sheet . '_hotelorder', $order);
		 * 循环所有的房间, 每间房存一个定单(这些房间房型是一样的) */
		$orderidlist = array();
		$a_roomcode = $this->c_hotelorder->getfreeroomcode($a_order); //取得绑定房号
        if($a_roomcode==false){
        	werr('没有可入住房间!');
        	return '';
        }
		if (count($a_roomcode) < $a_order['roomcount']) {
			//ajaxerr('剩余房间不足,请重新选择数量!');
			werr('没有足够可入住房间!');
			return '';
		}
		//stop($a_user,true);
		
		$a_order['guestinfo'] = $guestinfo[0];
		
		/* 绑定房号 */
		$a_order['roomcode'] = $a_roomcode['mycode'];
		$a_order['roomcodeid'] = $a_roomcode['id'];
		$a_order['roomtitle'] = $a_roomcode['title'];
		//$a_order['islockcode']=1;
		$GLOBALS['cache']->delete($a_user['crandcode'].$a_user['id']);
		$GLOBALS['cache']->save($a_user['crandcode'].$a_user['id'],$a_order,60*15);
		
		$str="酒店:".$a_order['hotelname'].'\n\r';
		$str.="房型:".$a_order['roomname'].'\n\r';
		$str.="入住时间:".date('Ymd',$a_order['mydate1']).'\n\r';
		$str.="离店时间:".date('Ymd',$a_order['mydate2']).'\n\r';
		$str.="总价:".$a_order['allprice'].'\n\r';
		$str.="支付以后将无法取消，确认现在支付么？";
		if($url==null){		
			jserr($str);
		}else{
			jserr($str,$url);
		}


	    	die;
		for ($i = 0; $i < $orderroomcount; $i++) {
			//入住人信息
			$a_order['guestinfo'] = $guestinfo[$i];

			/* 绑定房号 */
			$a_order['roomcode'] = $a_roomcode[$i]['mycode'];
			$a_order['roomcodeid'] = $a_roomcode[$i]['id'];
			
			/* //修改预订之后房间状态
			  $orderroomcode[] = $we->pdo->update(sheet . '_roomcode', $a_order); */

			//加入定单id列表
			$orderidlist[] = $this->we->pdo->insert(sheet . '_hotelorder', $a_order);

			$this->updatebookingcount($a_order); //更新预定数量
		}
		/* 提示信息 */

		/* 保存log */

		/* 发信息给酒店 */
		return implode(',', $orderidlist); /* 返回定单ID */
	}
	/*
	 * 特价房支付函数*
	 */	
    function specialpay(){
    	
    	// 提取个人信息
    	$sql = 'select * from `' . sheet . '_user` where 1 ';
    	$sql .= ' and id=' .$GLOBALS ['we']->user['id'];
    	$user = $GLOBALS ['we']->exeone($sql);
    	
    	$a_order=$GLOBALS['cache']->get($user['crandcode'].$user['id']);
    	//stop($a_order['allprice'].'-----'.$user['acanuse']);
    	if($a_order==false){
    		werr(1022);
    		return false;
    	}
    	
    	if($user['acanuse']<$a_order['allprice']){
    		werr('对不起，余额不足，请重新充值');
    		return false;
    	}
    	// 提取定单信息
    	$sql = 'select * from `' . sheet . '_hotelorder` where 1 ';
    	$sql .= ' and roomcode=' . $a_order['roomcode'];
    	$sql .= ' and roomcodeid=' .$a_order['roomcodeid'];
    	$sql .= ' and mydate1=' . $a_order['mydate1'];
    	$sql .= ' and (mystatus<>"cancel" and  mystatus<>"over")';
    	if($GLOBALS ['we']->exeone($sql)){
    		werr('对不起，此限时抢购房已抢购完毕，请重新选择');
    		return false;
    	}
    	
    	try {
    		loadc('c_money', 'cls_money');
    		$this->we->pdo->begintrans();
    	
    		$orderid = $GLOBALS['we']->pdo->insert(sheet . '_hotelorder', $a_order);
    		
    		$this->updatebookingcount($a_order);
    		// 存入财务数据
    		$GLOBALS['c_money']-> payspecailorder($orderid, $user);
    		$this->we->pdo->submittrans();
    	
    		return true;
    	} catch (PDOException $e) {
    		$this->we->pdo->rollbacktrans();
    		werr($e);
    		return false;
    	}
    }
	/*接收特价房的参数*/
	function getrequestoforder() {
		$we =& $GLOBALS['we'];
		
		$order = array();

		$order['roomcount'] = 1; //每个定单一间房, 这个和提交表单的房间数不一样

		$order['hotelid'] = $we->request('酒店ID', 'hotelid', 'post', 'num', 1, 999999);
		$order['roomid'] = $we->request('房型ID', 'roomid', 'post', 'num', 1, 999999);


		$order['mydate1'] = date('Y-m-d', time()); //特价房都是当天入住，转天离店
		$order['mydate2'] = date('Y-m-d', time() + 86400);

		$order['guestmobile'] = $we->request('联系手机', 'guestmobile', 'post', 'mobile', 11, 11);
		$order['guestmail'] = $we->request('Email', 'guestmail', 'post', 'mail', 6, 50, '', FALSE);

		$order['paytype'] = 'online'; //特价房都是到店支付
		$order['everyprice'] = '';


		return $order;
	}


	/*客人信息*/
	function getguestinfo( $roomcount,$countpersonofroom, $hotelid, $order) {
		$we = & $GLOBALS['we'];

		$roominfo = array(); //房间信息,是用户信息的组合		
		$codelist = array(); //身份证信息, 为了检测是不是有重复身份证

		/* 循环房间 */
		for ($i = 0; $i < $roomcount; $i++) {

			$guestinfo = array();
			/* 循环客人 */
			for ($j = 0; $j < $countpersonofroom; $j++) {

				/* 第一个必填 */
				if (0 == $j) {
					$mustfill = true;
				} else {
					$mustfill = false;
				}
				$name = $we->request('客人姓名', 'guestname_' . ( 1 ) . '_' . ($j + 1), 'post', 'char', 1, 20, 'encode', $mustfill);
				$code = $we->request('身份证号', 'guestcode_' . ( 1 ) . '_' . ($j + 1), 'post', 'identity', 1, 20, '', $mustfill);
				
				//ajaxerr();
				
				/*接收信息不对就返回*/
				if ( haserr() ) {
					return false;
				}

				/*验证码在平台上生成*/
				$randcode=$we->generate_randchar(6);
				
				if ('' !== $name) {
					/* 身份证号 */
					if ('' !== $code) {
						$codelist[] = $code;
						$guestinfo[] = $name . ',' . $code.','.''.','.'0';
					}
				}
			}
			$roominfo[] = implode(';', $guestinfo);
		}
		
		/*检测身份证是否有未完成的定单*/

		/* 检测提交的身份证是否有重复 */

		return $roominfo;
	}

    /*
     * 
     */
    function checkcodelistbyguestinfo($guestinfo,$a_order,$orderid=0){
    	
		if(is_array($guestinfo)){
    		for($i=0;$i<count($guestinfo);$i++){
    			$codelist[$i]=$this->getcodelist($guestinfo[$i]);
				if(count($codelist[$i])>0 && $codelist[$i][0]==!''){
					if($this->hasrepeatcode($a_order,$codelist[$i],$orderid)){
						werr('这个身份证'.$codelist[$i][0].'同一时段还有未完成定单！！');
						return false;
					}
				}
    		}
    	}else{
    		$codelist=$this->getcodelist($guestinfo);
			if(count($codelist)>0){
				if($this->hasrepeatcode($a_order,$codelist,$orderid)){
					werr('这个身份证'.$codelist[0].'同一时段还有未完成定单！！');
					return false;
				}
			}
    	}
    	return true;
    }

	 /*
     * 从guestinfo返回codelist也就是身份证list
     */
    function getcodelist($guestinfo){
    	if($guestinfo==null||$guestinfo=='')return false;

    	$name=array();
    	$codelist=array();
    	$s=array();
    	//stop($guestinfo,true);
    	if(strpos($guestinfo,';')){
    		$str=explode(';',$guestinfo);
    		for($i=0;$i<count($str);$i++){
    			if(!strpos($str[$i],',')){
    				return false;
    			}else{
    				$s=explode(',',$str[$i]);
    				$name[$i]=$s[0];
    				$codelist[$i]=$s[1];
    			}
    		}
    	}else{
    		if(strpos($guestinfo,',')){
    			$s=explode(',',$guestinfo);
    			$name[0]=$s[0];
    			$codelist[0]=$s[1];
    		}
    	}
    	return $codelist;
    }

	/*更新预定数量*/
	function updatebookingcount(&$order) {
		if($order['roomcount']<0){
			showerr(1022);
			exit();
		}
		$sql = 'update `' . sheet . '_specialroomlist` set ';
		$sql .= ' roomorder=roomorder+' . $order['roomcount']; //增加已售量
		$sql .= ' ,roomselled=roomselled+' . $order['roomcount']; //累计预定量
		$sql .= ' ,roomremain=roomremain-' . $order['roomcount'] . ' where 1 '; //剩余房量减一
		$sql .= ' and mydate >= ' . $order['mydate1'] . ' and mydate<' . $order['mydate2']; /* 在这个时间段内 */
		$sql .= ' and hotelid=' . $order['hotelid'];  //这个酒店
		$sql .= ' and roomid=' . $order['roomid'];  //这个房型
    //stop($sql);
		$this->we->execute($sql);
	}
	
	/* 检测身份证号重复性	
	 * 检测未入住的身份证,有没有这个号的
	 * 检测时还要带上酒店id
	 * $codelist : array
	 * 返回值 true=有重复, false=无重复
	 */

	function hasrepeatcode($order, $codelist, $orderid = 0) {
		$s = '';
		if (count($codelist) > 0) {
			if($codelist[0]=='')return false;
			foreach ($codelist as $v) {
				$s .= ' or guestinfo like "%' . $v . '%"';
			}
			/* 身份证填写人自身的检测 */
			$arr = count(array_unique($codelist));

			if (count($codelist) > $arr) {//判断下大小
				werr('有填写重复身份证，请重新填写！');
				return true;
			}
		} else {
			/* 没有身份证号,当然没有重复的了 */
			return false;
		}

		$sql = 'select * from `' . sheet . '_hotelorder` where 1 ';
		$sql .= ' and (mystatus="confirm" or mystatus="payed" or mystatus="livein") ';
		//$sql .= ' and hotelid=' . $hotelid; //这个酒店的定单
		//$sql .= ' and guestinfo like "%' . $v . '%" '; //这个酒店的定单
        /*if($order['mydate1']>0 && $order['mydate2']>0){
        	$sql .= 'and mydate1<='.$order['mydate1'].  ' and  mydate2>='.$order['mydate2'];
        }*/
		/* 有定单号了,证明是修改 */
		if (0 !== $orderid) {
			$sql .= ' and id<>' . $orderid;
		}


		/* like these idcode */
		/* 1>2 为了书写方便 */
		if ('' !== $s) {
			$sql .= ' and (1>2 ' . $s . ') ';
		}
		
		//stop($sql);
		$result = $GLOBALS['we']->execute($sql);
        if ($result['total']==0)return false;
        //stop($result,true);
		if ($result['total']>0) {
			if($this->checkorderdate($result['rs'],$order))	{
				werr('这个身份证还有未入住的房间或这个身份证在同一时段已订购别的酒店了!');
				return true;
			}else{
				return false;
			}	
		}
	}
	
	function checkorderdate($res,$order){
    	foreach ($res as $v) {
    		//时间段相同时
    		//if($v['mydate1']==$order['mydate1'] && $v['mydate2']==$order['mydate2'] )return true;
    		//订购单起始时间小于等于新订购起始时间
    		if($v['mydate1']<=$order['mydate1']){
    		if($v['mydate2']>$order['mydate1'])return true;
    		if($v['mydate2']>=$order['mydate2'])return true;
    		}
    		//订购单起始时间小于新订购结束时间
    		if($v['mydate1']<$order['mydate2']){
    			if($order['mydate1']<=$v['mydate1'])return true;
    		}
    	}
        return false;
    }
	
	//得出支付方式名称
    function paytypename($k) {
        if (array_key_exists($k, $this->paytype)) {
            return $this->paytype[$k];
        } else {
            showerr('支付方式错误!');
        }
    }
}

// end class





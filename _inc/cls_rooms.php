<?php

class Cls_Rooms {
	
	function __construct() {
		$this->we = &$GLOBALS['we'];
		
	}
	
	
	//手机接口调用房间相关信息
	//by zhengguorong
	//2015.6.2
	function getroomdata($hotelid,$date1,$date2){
		
		//$arr['err'] = '';
		
				
		//酒店ID
		if(isset($hotelid) && $hotelid!=''){
			$HotelIdSQLs = ' id = '.$hotelid.' ';
			$HotelIdSQL = ' and r.hotelid = '.$hotelid.' ';
			$HotelIdSQLss = ' and r.hotelid = '.$hotelid.' ';
		}else{
			$HotelIdSQLs = '1=1';
			$HotelIdSQL = '1=1';
			$HotelIdSQLss = '';
		}
		
		
		
		$whereroomlist = ''; //房间查询条件
		
		//入住时间，离店时间
		$hotelnow = strtotime(date('Y-m-d H:i:s',time()));
		
		/* date1 */
		$date1 = $GLOBALS['we']->request('入住时间', 'date1', 'get', 'date', 2, 50, '', 0);
		//stop($date1);
		if ('' == $date1) {
			$date1 = date('Y-m-d', time());
		} else {
			$unixdate1 = strtotime($date1);
			$today = strtotime(date('Y-m-d', time()));
			if ((ceil(($unixdate1-$today)/86400)) < 0) {
				jerr('入住时间不能早于今天');
			}
			$date1 = date('Y-m-d', $unixdate1);
		}
		
	
	
		/* date2 */
		$date2 = $GLOBALS['we']->request('离店时间', 'date2', 'get', 'date', 2, 50, '', 0);
		if ('' == $date2) {
			$date2 = date('Y-m-d', time() + 86400);
		} else {
			$unixdate2 = strtotime($date2);
	
			if ((ceil(($unixdate2-strtotime($date1))/86400)) <= 0) {
				jerr( '离店时间不能早于入住时间');
			}
			$date2 = date('Y-m-d', $unixdate2);
		}
		
		/* 入住时间 */
		$whereroomlist = ' and  rl.mydate>= ' . strtotime($date1) .' and rl.mydate<' . strtotime($date2) .' ';
		
		
		$sql1 = 'select id,telareacode,telfront,serverbase, serverbasename, mylocation, preimg, longitude,latitude, title,arrivetime1,arrivetime2,cityname from `'.sheet.'_hotel` where  '.$HotelIdSQLs.' limit 1';
		//stop($sql1);
		$li = $GLOBALS['we']->exeone($sql1);
		$hotel_img = explode('/',$li['preimg']);
		$last = array_pop($hotel_img);
		array_push($hotel_img,'thumb',$last);
		$url = 'http://'.'www.ejiayuding.com'.implode('/',$hotel_img);
		
		if(@fopen($url,'r')==false){
			$url = 'http://'.'www.ejiayuding.com'.'/_images/nopic.jpg';
		}
		$li['preimg'] = $url;
		
		if(!empty($li['serverbasename']) && !empty($li['serverbase'])){
			$a_h['title'] = explode(',',$li['serverbasename']);
			//echo '<pre>';
			//stop($a_h,true);
			$a_h['ic'] = explode(',',$li['serverbase']); 
			//echo '<pre>';
			//stop($a_h,true);
			foreach($a_h['ic'] as $k=>$v){
				foreach($a_h['title'] as $k1=>$v1){
					if(is_array($a_h)){
						$li['device'][$k]['ic'] = $v;
						$li['device'][$k1]['title'] = $v1;
					}else{
						$li['device'] = array();
					}
				}
			}
		}else{
			$li['device'] = array();
			$li['serverbase'] = '';	
			$li['serverbasename'] = '';
		}
		
		
		if(empty($li['mylocation'])){
			$li['mylocation'] = '';
		}
		
		if(empty($li['longitude'])){
			$li['longitude'] = '';
		}
		
		if(empty($li['latitude'])){
			$li['latitude'] = '';
		}

		$sql = 'select   r.id,r.r_netway, r.preimg,r.r_person, r.r_breakfirst, r.title, r.r_bed,rl.price, rl.roomremain  from `' . sheet . '_room` r ';
		$sql .= ' inner join `'.sheet.'_roomlist` rl on r.id = rl.roomid ';
		$sql .= ' where 1=1 ';
		$sql .= ' and r.isuse=1 ';
		$sql .= ' and r.isspecial=0';
		$sql .= ' '.$whereroomlist.' ';
		$sql .= ''.$HotelIdSQLss.'';
		$sql .= ' and rl.price <>0';
		$sql .= ' order by rl.price asc';
		//stop($sql);
		$a_room = $GLOBALS['we']->execute($sql);
		
		$a_room_preimg = array();
		foreach($a_room['rs'] as $key=>$value){
			
			$a_room_preimg = $value['preimg'];
			//$harr = explode('/',$a_room_preimg);
			//$last = array_pop($harr);
			//array_push($harr,'thumb',$last);
			//$url = 'http://'.'www.ejiayuding.com'.implode('/',$harr);
			
			//if(@file_get_contents($url,'r')==false){
				//$url = 'http://'.'www.ejiayuding.com'.'/_images/nopic.jpg';
			//}
			
			if(isset($a_room_preimg)&&!empty($a_room_preimg)&&is_array($a_room_preimg)){
				$url = $j['servername'].thumbimg($hotelarr);
			}else{
				$url = $j['servername'].'/_images/nopic.jpg';	
			}
			$a_room['rs'][$key]['preimg'] = $url;
			
			
		}
		$li['rooms'] = $a_room['rs'];
		$arr['hotel'] = $li;
		if($li==false){
			$arr['hotel']=array();
		}
		return $arr;
	}

}
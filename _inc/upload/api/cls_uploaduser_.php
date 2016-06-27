<?php
// require('../global.php');
//下面的路径按照你PHPExcel的路径来修改
require_once sysdir.'_phpexcel/PHPExcel.php';
require_once sysdir.'_phpexcel/PHPExcel/IOFactory.php';
require_once sysdir.'_phpexcel/PHPExcel/Reader/Excel5.php';
/**
 * 上传用户
 */
class Cls_Uploaduser {
//  	public $hotelid;
	public $uploadfile; //上传后的文件名地址
	public $data;
	public $keys;      //有效数据的索引
	
	//上传文件
	function uploadFile($file,$filetempname){
		$filePath = sysdir.'_upload/files/';
		
		//注意设置时区
		$time=date("ymdHis");//去当前上传的时间
		//获取上传文件的扩展名
		$extend=strrchr ($file,'.');
		//上传后的文件名
		$name="user".$time.$extend;
		$this->uploadfile=$filePath.$name;//上传后的文件名地址
		//move_uploaded_file() 函数将上传的文件移动到新位置。若成功，则返回 true，否则返回 false。
		$result = move_uploaded_file($filetempname,$this->uploadfile);//假如上传到当前目录下
		return $result;
	}
	
	//从上传的excel文件读取数据
	function getexceldata(){	
        $fileType = PHPExcel_IOFactory::identify($this->uploadfile); //文件名自动判断文件类型
// 		stop($fileType);
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($this->uploadfile);
		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow();           //取得总行数
		$highestColumn = $sheet->getHighestColumn(); //取得总列数	
		$objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数

		$headtitle=array();
		$this->data = array(); //最终用户数据
		for($col = 0;$col < $highestColumnIndex;$col++){
			$headtitle[$col] = $objWorksheet->getCellByColumnAndRow($col,2)->getValue();
		}
		for ($row = 3;$row <= $highestRow;$row++)
		{		
			//注意highestColumnIndex的列数索引从0开始
			for ($col = 0;$col < $highestColumnIndex;$col++)
			{
				$cell = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
				if($cell instanceof PHPExcel_RichText)     //富文本转换字符串  
                    $cell = $cell->__toString();
                if($cell!=''){
				    $this->data[$row-3][$headtitle[$col]] = $cell;
				}
				if($col==($highestColumnIndex-1)){
					$this->data[$row-3]['iseffect'] = 0;
				}
				// if($col=1)
				// stop($col.$row);
			}
			 //是否有效		   
		}
		
 		//stop($this->data,true);
		return $this->data;		
	}
	
	//验证上传数据是否有已存在的手机号，是否有重复的手机号
	function mobiledetection(){
		$uneffect = array();
		if(!function_exists("array_column"))
		{		
			function array_column($array,$column_name)
			{
		
				return array_map(function($element) use($column_name){return $element[$column_name];}, $array);		
			}		
		}
		$mobile = array_filter(array_column($this->data,'手机号')); //上传的手机号
 		$mobilelist = implode(",",$mobile);
 		$current = count($mobile);   //本次导入的数据量
// 		stop($mobile,true);
 		//验证数据库中是否有该手机号的会员
		$sql = "select u_mobile from `".sheet."_user` where u_mobile in (".$mobilelist.")";
		$sql .= " and isdel=0";
		$sql .= " and u_gic='member'";
//  		stop($sql);
		$umrs = $GLOBALS['we']->executers($sql); 
		foreach ($umrs as $v){
			$uneffect[] = $v['u_mobile'];     //无效的
		}
		// $sql = "select u_mobile from `".sheet."_uploaduser` where u_mobile in (".$mobilelist.")";
		// $upuser = $GLOBALS['we']->executers($sql);
		// if($upuser!=false){
		//     foreach ($upuser as $v){
		// 	    $uneffect[] = $v['u_mobile'];     //无效的
		//     }
		// }
	
		if (count($uneffect) != 0) {
			$effect_u = array_diff($mobile,$uneffect);  //相对user表有效的
		}
		else {
			$effect_u = $mobile;
		}
    	
// 		stop($effect_u,true);
		// 检测上传数据是否有重复手机号
		$effect = array_unique($effect_u);            //最终有效的手机号    
		$this->keys = array_keys($effect);
		   
		for ($i=0; $i<count($effect); $i++){
			$this->data[$this->keys[$i]]['iseffect'] = 1;
		}
		return $current;
		
	}
	
	//向数据库插入数据
	function insertdata($hotelid){
		$suid = $GLOBALS['we']->user['id'];    //上传人
		$snick = $GLOBALS['we']->user['u_nick'];
		$stimeint = time();                    //上传时间
		$stime = date("Y-m-d H:i:s",time());
		$sql = 'select title from `'.sheet.'_hotel` where id='.$hotelid;
		$hoteltitle = $GLOBALS['we']->exeone($sql)['title'];
		$upnum =10;       //一次插入的数据条数

		try {
			$GLOBALS['we']->pdo->begintrans();
			//向uploaduser插入数据
			$num = count($this->data);	
			$loop = intval($num/$upnum);
			$sum1 = 0;
			$iseffect = 0;
			$sql0 = 'insert into `'.sheet.'_uploaduser` (u_mobile,u_nick,u_pass,u_idcode,';
			$sql0 .= 'stimeint,stime,hotelid,hoteltitle,suid,snick,randcode,iseffect)  values';
			for($j = 1; $j <= $loop; $j++){
				$sql = $sql0;
			    for ($i = 0; $i < $upnum;){
			    	if(isset($this->data[$sum1]['手机号'])&&$this->data[$sum1]['手机号'] != ''){
						$u_mobile = $this->data[$sum1]['手机号'];
						$iseffect = $this->data[$sum1]['iseffect']?:0;
						if(isset($this->data[$sum1]['身份证号']))$idcode = $this->data[$sum1]['身份证号']?:0;
						$u_nick = substr( $u_mobile, 0 , 7) . $GLOBALS['we']->generate_randchar(4); //默认的昵称——电话前7位加4位随机字符串
						//随机码和默认密码
						$randcode = $GLOBALS['we']->generate_randchar(8);
						$u_pass = md5('111111' . $randcode);
				
						$sql .=  '("'.$u_mobile.'","'.$u_nick.'","'.$u_pass.'","'.trim($idcode).'","'.$stimeint;
						$sql .= '","'.$stime.'","'.$hotelid.'","'.$hoteltitle.'","'.$suid.'","'.$snick.'","'.$randcode.'","'.$iseffect.'")';
				
						if ($i != $upnum -1 )
						{
							$sql .= ',';
						}
						
			    	}
			    	$i++;
			    	$sum1++;
				}
				//if($j==2)stop('1'.'--'.$sql);
 				//stop('1'.'--'.$sql);
 			    if(substr(trim($sql),-1,1)==',')$sql=substr(trim($sql),0,-1);
 				// if($j==2)stop('1'.'--'.$sql);
 			   // if($j==3)stop(22222);
				if(substr(trim($sql),-6,6)!='values')$GLOBALS['we']->execute($sql);
				//if($j==2)stop('1'.'--'.$sql);
				//$sql = '';
			}
			if ($sum1 < $num) {
				$k = $num - $sum1;
				//$sql = $sql0;
				$sql1='';
				for ($i = 0; $i < $k; ){
					if(isset($this->data[$sum1]['手机号'])&&$this->data[$sum1]['手机号'] != ''){
						$u_mobile = $this->data[$sum1]['手机号'];
						$iseffect = $this->data[$sum1]['iseffect']?:0;
						//$idcode = $this->data[$sum1]['身份证号'];
						if(isset($this->data[$sum1]['身份证号']))$idcode = $this->data[$sum1]['身份证号']?:0;
						$u_nick = substr( $u_mobile, 0 , 7) . $GLOBALS['we']->generate_randchar(4); //默认的昵称——电话前7位加4位随机字符串
						//随机码和默认密码
						$randcode = $GLOBALS['we']->generate_randchar(8);
						$u_pass = md5('111111' . $randcode);
							
						$sql1 .= '("'.$u_mobile.'","'.$u_nick.'","'.$u_pass.'","'.$idcode.'","'.$stimeint;
						$sql1 .= '","'.$stime.'","'.$hotelid.'","'.$hoteltitle.'","'.$suid.'","'.$snick.'","'.$randcode.'","'.$iseffect.'")';
							
						if ($i != $k -1 ) {
							$sql1 .= ',';
						}
						
					}
					$i++;
					$sum1++;
				}
				if($sql1!=''){
				    //stop('2'.'--'.$sql0.$sql1);
					$sql111=trim($sql0.$sql1);
				    if(substr(trim($sql111),-1,1)==',')$sql111=substr(trim($sql111),0,-1);
					$GLOBALS['we']->execute($sql111);
				}
				
			}
			//向user导入数据
			$enum = count($this->keys);
			$eloop = floor($enum / $upnum);
			$sum2 = 0;
			$u_endtimeint	= time()+86400*3650;
			$u_endtime = date("Y-m-d H:i:s", $u_endtimeint);
			
			$sql0 = 'insert into `'.sheet.'_user` (u_gic,u_gname,u_mobile,u_nick,u_pass,u_idcode,';
			$sql0 .= 'u_regtimeint,u_regtime,stimeint,stime,suid,snick,randcode,islock,ispass,u_face,';
			$sql0 .= 'u_endtimeint,u_endtime,u_source) values';
			for($j = 1; $j <= $eloop; $j++){
				$sql = $sql0;
				for ($i = 0; $i < $upnum; $i++){	
					$u_mobile = $this->data[$this->keys[$sum2]]['手机号'];
					$idcode = $this->data[$this->keys[$sum2]]['身份证号'];
					$u_nick = substr( $u_mobile, 0 , 7) . $GLOBALS['we']->generate_randchar(4); //默认的昵称——电话前7位加4位随机字符串
					//随机码和默认密码
					$randcode = $GLOBALS['we']->generate_randchar(8);
					$u_pass = md5('111111' . $randcode);
			
					$sql .= '("member","会员","'.$u_mobile.'","'.$u_nick.'","'.$u_pass.'","'.$idcode.'","';
					$sql .= $stimeint.'","'.$stime.'","'.$stimeint.'","'.$stime.'","'.$suid.'","'.$snick.'","';
					$sql .= $randcode.'","0","1","/_images/noface.png","'.$u_endtimeint.'","'.$u_endtime.'","';
			        $sql .= $GLOBALS['config']['source']['upload'].'")';
					if ($i != $upnum -1 ) {
						$sql .= ',';
					}
					$sum2++;
				}
                $GLOBALS['we']->execute($sql);
// 				$sql = '';
				
			}
				
			if ($sum2 < $enum) {
				$k = $enum - $sum2;
				echo $k;
				$sql = $sql0;
				for ($i = 0; $i<$k;$i++){
					$u_mobile = $this->data[$this->keys[$sum2]]['手机号'];
					$idcode = $this->data[$this->keys[$sum2]]['身份证号'];
					$u_nick = substr( $u_mobile, 0 , 7) . $GLOBALS['we']->generate_randchar(4); //默认的昵称——电话前7位加4位随机字符串
						//随机码和默认密码
					$randcode = $GLOBALS['we']->generate_randchar(8);
					$u_pass = md5('111111' . $randcode);
				
					$sql .= '("member","会员","'.$u_mobile.'","'.$u_nick.'","'.$u_pass.'","'.$idcode.'","';
					$sql .= $stimeint.'","'.$stime.'","'.$stimeint.'","'.$stime.'","'.$suid.'","'.$snick.'","';
					$sql .= $randcode.'","0","1","/_images/noface.png","'.$u_endtimeint.'","'.$u_endtime.'","';
					$sql .= $GLOBALS['config']['source']['upload'].'")';
					if ($i != $k - 1) {
						$sql .= ',';
					}
					$sum2++;
				}
				$GLOBALS['we']->execute($sql);
				
			}	
			$GLOBALS['we']->pdo->submittrans();	
			return true;
		} catch (PDOException $e) {
			$GLOBALS['we']->pdo->rollbacktrans();
			werr($e);
			return false;
		}
			
		
	}

	//获取上传的用户信息
	function getuploaduser($hotelid,$searchtype='',$mobile=''){
		$sql = 'select *,(case when iseffect=1 then "有效" else "无效" end) as iseffect';
		$sql .= ' from `'.sheet.'_uploaduser` where hotelid='.$hotelid;
		if($searchtype != ''){
			$sql .= ' and iseffect='.$searchtype;
		}
		if ($mobile != '') {
			$sql .= ' and u_mobile="'.$mobile.'"';
		}
		$sql .= ' order by id desc';
		return $sql;
	}
}

?>
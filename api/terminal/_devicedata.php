<?php
require '../../global.php';
//require '../../api/door/door.php';
htmlmain();
function htmlmain(){ 
    $xml = '';
    $xml['err'] = '';
    $xml['success'] = 0;

    if (isset($_SESSION['orderid'])) {
        $orderid = $_SESSION['orderid'];
        $sql = 'update `'.sh.'_order` set doorstatus="open" where id='.$orderid;
        $orid = $GLOBALS['pdo']->doSql($sql);
       
    }

    $device = $GLOBALS['main']->request('device','设备号',12,12,'char','invalid');
    $alldoor = $GLOBALS['main']->request('alldoor','所有柜门号',1,255,'char');
    $sdoor = $GLOBALS['main']->request('sdoor','打开的柜门号',1,255,'char','',false);
    //$success = $GLOBALS['main']->request('success','开门结果',0,2,'num','invalid');

    if (!empty($GLOBALS['errmsg'])) {
		$xml['err'] = $GLOBALS['errmsg'];
		echo ( json_encode($xml) );
	    die;
	}
      
    //在维修队列中的柜门号被打开，更新记录isend
    if ('' !== $sdoor) {
        $finish = findoor($device,$sdoor);
         
        foreach ($finish as $val) {
             $sql = 'update `' .sh. '_failtofix` set isend=1,repairtime="'. date('Y-m-d H:i:s', time()).'" where 1=1 and type="门锁问题"';
             $sql .= ' and deviceic=:deviceic and door=:door';
             $GLOBALS['pdo']->doSql($sql,Array(':deviceic'=>$device,':door'=>$val));
             
        }
    }
   
   // print_r($finish);
   // die();
	$alldoorarr = explode(',', $alldoor);
	if ('' !== $sdoor) {
		$fdoorarr = array();
		$sdoorarr = explode(',', $sdoor);
		foreach ($alldoorarr as $val) {
			if (!in_array($val, $sdoorarr)) {
				$fdoorarr[] = $val;
			}
		}
	}else{
		$sdoorarr = array();
		$fdoorarr = $alldoorarr;
	}
    
    if (!empty($fdoorarr)) {//有没打开的柜门
        //sleep(2);
    	//1.查询是否有相同的柜门在维修队列，有重复的则不再进队列
        
       // $fdoor = implode(',', $fdoorarr);
        //$temp = findoor($device,$fdoor);
      
       // $hb = array_diff($fdoorarr, $temp);
       // $fdoorarr = $hb;
      
 
        //2.对筛选完的柜门号加进维修队列
        if (!empty($fdoorarr)) {
   
            $info = getinfo($device);
            $info['deviceic'] = $device;
            $info['type'] = '门锁问题';
            $info['stimeint'] = time();
            $info['stime'] = date('Y-m-d H:i:s',$info['stimeint']);
            foreach ($fdoorarr as $v) {
                  $info['door'] = $v;
                  $backid = $GLOBALS['pdo']->insert(sh.'_failtofix',$info);
            }
        }
        
        //$fdoor = implode(',', $fdoorarr);
    	//$url = 'http://111.160.198.250:1615/api/door/door.php?deviceic='.$device.'&dooridlist='.$fdoor;
    	//$url = 'http://www.ejshendeng.com/api/door/door.php?deviceic='.$device.'&dooridlist='.$fdoor;

        /*try{
            $contents = file_get_contents($url);
        }  catch(Exception  $e){
            $this->ckerr('神灯服务器连接失败');
          
        }
        $res = json_decode($contents,true);
        if (trim($res['idlist'],',') !== $fdoor) {
        	$back['success'] = 0;
        	echo json_encode($back);
        	die;
        }*/
        //stop(trim($res['idlist'],','),true);
    	//$myclassapi = new myapi();
    	//$myclassapi->main($device,$fdoor);
    	
    }
 
    //stop($fdoorarr,true);
	/*测试记录日志*/
	/*$str = $device.'=='.$alldoor.'=='.$sdoor;
    $dir = syspath . 'api/terminal/' . time() . '.txt';
	$GLOBALS['main']->write_file($dir, $str);*/
	
	$back['success'] = 1;
	echo json_encode($back);
	die;
  }

  function getinfo($device){
     $sql = 'select c.title as comname,c.id as comid,c.mylocation as address,c.telfront as tel,p.building,p.floor,p.title,p.id as placeid from `' .sh. '_device` as d,`'.sh.'_com` as c,`'.sh.'_place` as p where d.comid=c.id';
     $sql .= ' and d.ic=:device';
     $sql .= ' and d.placeid=p.id';
     $res = $GLOBALS['pdo']->fetchOne($sql, Array(':device' => $device));
     return $res;
  }

  function findoor($device,$doorstr){ 
     $sql = 'select door from `' .sh. '_failtofix` where isend=0 and deviceic=:deviceic';
     $sql .= ' and door in ('.$doorstr.')';
     $ha = $GLOBALS['pdo']->fetchAll($sql, Array(':deviceic' => $device));
     $temp = array();
     foreach ($ha as $val) {
           $temp[] = $val['door'];
     }
     return $temp;
  }


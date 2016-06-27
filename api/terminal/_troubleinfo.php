<?php

/*机体倾斜*/
require '../../global.php';
htmlmain();

function htmlmain(){ 
    $xml = '';
    $xml['success'] = 0;

    $device = $GLOBALS['main']->request('device','设备号',12,12,'char','invalid');
    //$fixinfo = $GLOBALS['main']->rqid('fixinfo');

    if (!empty($GLOBALS['errmsg'])) {
		echo ( json_encode($xml) );
	    die;
	}

    $dvcinfo = getinfo($device);

    if (empty($dvcinfo)) {
    	echo (json_encode($xml));
    	die;
    }
    $dvcinfo['deviceic'] = $device;
    $dvcinfo['door'] = 0;
    $dvcinfo['type'] = '机体倾斜';
    $dvcinfo['stimeint'] = time();
    $dvcinfo['stime'] = date('Y-m-d H:i:s',$dvcinfo['stimeint']);
    $GLOBALS['pdo']->insert(sh.'_failtofix',$dvcinfo);
    $xml['success'] = 1;
    echo (json_encode($xml));
    die;
}

function getinfo($device){
     $sql = 'select c.title as comname,c.id as comid,c.mylocation as address,c.telfront as tel,p.building,p.floor,p.title,p.id as placeid from `' .sh. '_device` as d,`'.sh.'_com` as c,`'.sh.'_place` as p where d.comid=c.id';
     $sql .= ' and d.ic=:device';
     $sql .= ' and d.placeid=p.id';
     $res = $GLOBALS['pdo']->fetchOne($sql, Array(':device' => $device));
     return $res;
}
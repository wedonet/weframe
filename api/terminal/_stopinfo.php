<?php
/*处理单片机服务器打不开门情况*/

require'../../global.php';

savestop();

function savestop() {
    $xml = '';
    $xml['success'] = 0;
    
    $device = $GLOBALS['main']->request('device', '设备号', 12, 12, 'char', 'invalid');
    $isstop = $GLOBALS['main']->request('isstop', '连接还是掉线', 0, 1, 'int', 'invalid');
    
    if (!empty($GLOBALS['errmsg'])) {
        // echo(json_encode($xml));
        die;
    }
    $dvcinfo = getinfo($device);

    if (empty($dvcinfo)) {
        // echo(json_encode($xml));
        die;
    }
    $sqlsela = 'select * from `' . sh . '_device` where ic="' . $device . '"';
    
    $resa = $GLOBALS['pdo']->fetchOne($sqlsela);
    
    if (count($resa) > 0) {
        updatafail($device, $dvcinfo, $isstop);
    }
}

function updatafail($deviceid, $dvcinfo, $isstop) {

    if ('0' == $isstop) {//0设备掉线
        $dvcinfo['deviceic'] = $deviceid;
        $dvcinfo['door'] = 0;
        $dvcinfo['type'] = '心跳包异常';
        $dvcinfo['stimeint'] = time();
        $dvcinfo['isend'] = 0;
        $dvcinfo['stime'] = date('Y-m-d H:i:s', $dvcinfo['stimeint']);
        $eee = $GLOBALS['pdo']->insert(sh . '_failtofix', $dvcinfo);

        $sqldevice = 'update `' . sh . '_device` set mystatus="down" where ic="' . $deviceid . '"';
        $faildevice = $GLOBALS['pdo']->doSql($sqldevice);

        $xml['success'] = 1;
        echo(json_encode($xml));
        die;
    } else if ('1' == $isstop) {
        $sqlsel = 'select * from `' . sh . '_failtofix` where deviceic="' . $deviceid . '" and isend=0';
        $res = $GLOBALS['pdo']->fetchAll($sqlsel);

        foreach ($res as $v) {
            $sql = 'update `' . sh . '_failtofix` set isend=1 , repairtime="' . date('Y-m-d H:i:s', time()) . '" where type="心跳包异常" and id=' . $v['id'];
            $fail = $GLOBALS['pdo']->doSql($sql);
        }

        $sqldevice = 'update `' . sh . '_device` set mystatus="doing" where ic="' . $deviceid . '"';
        $faildevice = $GLOBALS['pdo']->doSql($sqldevice);
        $xml['success'] = 1;
        echo(json_encode($xml));
        die;
    }
}

function getinfo($device) {
    $sql = 'select c.title as comname,c.id as comid,c.mylocation as address,c.telfront as tel,p.building,p.floor,p.title,p.id as placeid from `' . sh . '_device` as d,`' . sh . '_com` as c,`' . sh . '_place` as p where p.comid=c.id';
    $sql .= ' and d.ic=:device';
    $sql .= ' and d.placeid=p.id';
    $res = $GLOBALS['pdo']->fetchOne($sql, Array(':device' => $device));

    return $res;
}

?>

<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once AdminApiPath . '_main.php';
require_once '_main.php'; /* 业务管理通用数据 */

/* */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        $this->comid = $this->main->rid('comid');

        $c_company = new cls_companymain();

        /* 店铺信息添加进$globals['j']['company'] */
        $this->j['company'] = $c_company->getcompany($this->comid);




        $this->act = $this->main->ract();

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;
            case'select';
                $this->selectdevice();
                $this->output();
                break;
            case'creat';
                $this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getdevice();
                $this->output();
                break;
            case 'selplace':
                $this->selplace();
                $this->output();
                break;
            case 'copy':
                $this->formcopy();
                $this->output();
                break;




            case 'nsave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savenew();
                $this->output();
                break;
            case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;
            case 'setplace':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->setplace();
                $this->output();
                break;
            case 'past':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dopast();
                $this->output();
                break;

            case 'isrun':
            case 'unrun':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->doadmin();
                $this->output();
                break;
        }
    }

    function mylist() {
        $sql = 'select device.*,place.title as placetitle ';
        $sql .= ' ,place.building as building ';
        $sql .= ' ,place.floor as floor ';
        $sql .= ' from `' . sh . '_device` as device  ';
        $sql .= ' left join `' . sh . '_place` as place on device.placeid=place.id ';
        $sql .= ' where 1';
        $sql .= ' and device.comid=:comid';

        $result = $this->pdo->fetchAll($sql, Array(':comid' => $this->comid));


        $this->j['list'] = $result;
    }

    /* 提取平台的全设备 */

    function formedit() {
        
    }

    function myform() {
        
    }

    function getdevice() {
        /* 取详细信息 */
        $a['id'] = 1;
        $a['ic'] = 'A1';
        $a['typeic'] = 'AAA机型';
        $a['doornum'] = '24';
        $a['isrun'] = '是'; //是否运行
        $a['placeic'] = 'A1';
        $a['placeid'] = 1;
        $a['comid'] = '汇川酒店'; //属于哪个店铺    

        $GLOBALS['j']['device'] = $a;
    }

    function esave() {
        if (1 == 1) {
            $this->j['success'] = 'y';
            $this->j['msg'] = '保存成功';
        } else {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '错误1';
            $this->j['errmsg'][] = '错误2';
            $this->j['errinput'] = 'u_mobile';
        }
    }

    function savenew() {
        $we = $GLOBALS['main'];
        $we->posttype = 'post';

        $rs['typeic'] = $we->request('typeic', '机型', 2, 20, 'char', 'invalid');
        $rs['ic'] = $we->request('ic', 'IC', 2, 20, 'char', 'invalid');

        $this->ckerr();

        /* 检测是否有这个机型 */
        if (!array_key_exists($rs['typeic'], $this->devicetype)) {
            $this->ckerr('没找到这个机型');
        }

        /* 检测ic重复 */
        if ($we->hasic(sh . '_device', -1, $rs['ic'])) {
            $this->ckerr('已经有这个编码了,请重新输入');
        }

        /* 添加设备 */
        $rs['comid'] = $this->comid;
        $id = $this->pdo->insert(sh . '_device', $rs);

        /* 插入门的表 */
        $typeic = $rs['typeic'];
        $deviceid = $id;
        $deviceic = $rs['ic'];

        unset($rs);
        for ($i = 0; $i < $this->devicetype[$typeic]['doornum']; $i++) {
            $rs['title'] = $i + 1;
            $rs['hasgood'] = 0;
            $rs['mystatus'] = 'running';
            $rs['doorstatus'] = 'close';

            $rs['deviceid'] = $deviceid;
            $rs['deviceic'] = $deviceic;

            $rs['goodsic'] = '';
            $rs['goodsid'] = 0;

            $rs['comid'] = $this->comid;

            $id = $this->pdo->insert(sh . '_door', $rs);
        }

        $this->j['success'] = 'y';
    }

    function selplace() {
        $deviceid = $this->main->rqid('deviceid');

        /* 设备信息 */
        $sql = 'select * from `' . sh . '_device` where 1 ';
        $sql .= ' and comid=:comid ';
        $sql .= ' and id=:id';

        $device = $this->pdo->fetchOne($sql, Array(':comid' => $this->comid, ':id' => $deviceid));

        $this->j['device'] = $device;

        /* 当前铺位 */
        $placeid = $device['placeid'];

        if ($placeid > 0) {
            $sql = 'select * from `' . sh . '_place` where 1 ';
            $sql .= ' and id=:placeid ';
            $sql .= ' and comid=:comid ';

            $place = $this->pdo->fetchOne($sql, Array(':comid' => $this->comid, ':placeid' => $placeid));

            $this->j['place'] = $place;
        }
    }

    /* 保存铺位 */

    function setplace() {
        $we = $GLOBALS['main'];
        $deviceid = $we->rfid('deviceid');

        $we->posttype = 'post';
        $rs['placeid'] = $we->request('placeid', '铺位ID', 0, 999999, 'int');
        $rs['placeic'] = $we->request('placeic', '铺位IC', 1, 20, 'char', 'invalid', false);

        $this->ckerr();

        /* 把设备移出铺位 */
        if (0 == $rs['placeid'] AND '' == $rs['placeic']) {
            /* 没有铺位了，变成不运行状态 */
            $rs['isrun'] = 0;

            /* 执行更新 */
            $this->pdo->update(sh . '_device', $rs, 'id=:deviceid', Array(':deviceid' => $deviceid));
            $this->j['success'] = 'y';

            /* 更新柜门所属位置 */
            $sql = 'update `' . sh . '_door` set placeid=0 where 1 ';
            $sql .= ' and deviceid=:deviceid ';

            $this->pdo->doSql($sql, Array(':deviceid' => $deviceid));

            return;
        }

        /* 检测铺位对不对，可以一个地放多个设备 */
        $sql = 'select count(*) from `' . sh . '_place` where 1 ';
        $sql .= ' and id=:id';
        $sql .= ' and ic=:ic';
        $sql .= ' and comid=:comid';

        $counts = $this->pdo->counts($sql, Array(':id' => $rs['placeid'], ':ic' => $rs['placeic'], ':comid' => $this->comid));

        if ($counts == 0) {
            $this->ckerr('没找到这个铺位');
        }

        /* 执行更新 */
        $this->pdo->update(sh . '_device', $rs, 'id=:deviceid', Array(':deviceid' => $deviceid));

        /* 更新门所属铺位 */
        $sql = 'update `' . sh . '_door` set placeid=' . $rs['placeid'] . ' where 1 ';
        $sql .= ' and deviceid=:deviceid ';

        $this->pdo->doSql($sql, Array(':deviceid' => $deviceid));


        $this->j['success'] = 'y';
    }

    function doadmin() {
        $id = $this->main->rqid();

        /* 提店铺信息 */
        $sql = 'select * from `' . sh . '_device` where 1 ';
        $sql .= ' and id=:id ';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $rs = Array();

        switch ($this->act) {
            case 'isrun':
                if (1 == $result['isrun']) {
                    $this->ckerr('已经是运行状态了，不需要重复操作');
                } else {
                    $rs['isrun'] = 1;
                }
                break;
            case 'unrun':
                if (0 == $result['isrun']) {
                    $this->ckerr('已经是停止状态了，不需要重复操作');
                } else {
                    $rs['isrun'] = 0;
                }
                break;
        }

        $this->pdo->update(sh . '_device', $rs, 'id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    function formcopy() {
        $deviceid = $this->main->rqid('deviceid');

        /* 设备信息 */
        $sql = 'select * from `' . sh . '_device` where 1 ';
        $sql .= ' and comid=:comid ';
        $sql .= ' and id=:id';

        $device = $this->pdo->fetchOne($sql, Array(':comid' => $this->comid, ':id' => $deviceid));

        $this->j['device'] = $device;


        /* 当前铺位 */
        $placeid = $device['placeid'];

        if ($placeid > 0) {
            $sql = 'select * from `' . sh . '_place` where 1 ';
            $sql .= ' and id=:placeid ';
            $sql .= ' and comid=:comid ';

            $place = $this->pdo->fetchOne($sql, Array(':comid' => $this->comid, ':placeid' => $placeid));

            $this->j['place'] = $place;
        }
    }

    /* 保存铺位 */

    function dopast() {
        $we = $GLOBALS['main'];
        $sourcedeviceid = $we->rfid('sourcedeviceid');



        $we->posttype = 'post';
        $rs['deviceid'] = $we->request('deviceid', '设备ID', 0, 999999, 'int');
        $rs['deviceic'] = $we->request('deviceic', '设备IC', 1, 20, 'char', 'invalid', false);

        $this->ckerr();

        /* 检测目标设备不能等于原设备 */
        if ($sourcedeviceid == $rs['deviceid']) {
            $this->ckerr('原设备不能和目标设备相同');
        }


        /* 检测目标设备对不对 */
        $sql = 'select count(*) from `' . sh . '_device` where 1 ';
        $sql .= ' and id=:id';
        $sql .= ' and ic=:ic';
        $sql .= ' and comid=:comid';

        $counts = $this->pdo->counts($sql, Array(':id' => $rs['deviceid'], ':ic' => $rs['deviceic'], ':comid' => $this->comid));

        if (0 == $counts) {
            $this->ckerr('只能复制到本店铺的设备中');
        }

        /* 提取原设备商品 */
        $sql = 'select * from `' . sh . '_door` where deviceid=:sourcedeviceid';
        $result = $this->pdo->fetchAll($sql, Array(':sourcedeviceid' => $sourcedeviceid));

        /* 执行更新 */
        
         $s1 = 'update `' . sh . '_device` set goodsnum=0';        
         $s1.= ' where id=' . $rs['deviceid'];
         $s1.= ' and comid=' . $this->comid;
         $this->pdo->doSql($s1);  
         
        $t = Array();

        foreach ($result as $v) {
            $v['comgoodsid'] = $v['comgoodsid'] == '' ? 0 : $v['comgoodsid'];

            $s = 'update `' . sh . '_door` set goodsid=' . $v['goodsid'];
            $s.= ',goodsic="' . $v['goodsic'] . '"';
            $s.= ',comgoodsid=' . $v['comgoodsid'];
            $s.= ',hasgoods=0';
            $s.= ' where deviceid=' . $rs['deviceid'];
            $s.= ' and title="' . $v['title'] . '"';
            $s.= ' and comid=' . $this->comid;
            //$t[] = $s; 
            $this->pdo->doSql($s);
        }




        $this->j['success'] = 'y';
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
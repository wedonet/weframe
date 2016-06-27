<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
require_once ApiPath . '_adminxxx1/_main.php';
/* 返回用户组 */

class admin_business_device extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }


        /* 设备类型 */
        $this->devicetype['T001']['doornum'] = 22;
        $this->devicetype['T002']['doornum'] = 23;
        $this->devicetype['T003']['doornum'] = 22;
        $this->devicetype['T004']['doornum'] = 16;

        $this->act = $this->main->ract();

        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
            case 'creat':
                //$this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getdata();
                $this->output();
                break;
            case 'admin': //管理用户
                $this->getdata();
                $this->output();
                break;

            case 'belong':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dobelong();
                $this->output();
                break;

            case 'isrun':
            case 'unrun':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->doadmin();
                $this->output();
                break;
            case 'nsave': //保存新用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savenew();
                $this->output();
                break;
            case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;
            case 'savepass':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savepass();
                $this->output();
                break;
            case 'del': //删除用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dodel();
                $this->output();
        }
    }

    /* 用户列表 */

    function pagemain() {
        $this->main->posttype = 'get';
        /* 接收参数 */
        $mystatus = $this->main->request('mystatus', '状态', 1, 10, 'char', 'invalid', false);
        $ic = $this->main->request('ic', '设备ic', 1, 50, 'char', '', false);
        $comtitle=$this->main->request("comtitle","店铺名称",0,255,'char');



        /* 传回前端 */
        $this->j['search']['ic'] = $ic;
        $this->j['search']['mystatus'] = $mystatus;
         $this->j['search']['comtitle'] = $comtitle;
         
        /* 列表显示 */
        $sql = ' select device.*, com.title as comname from `' . sh . '_device` as device';
        $sql .= ' left join `' . sh . '_com` as com on device.comid=com.id ';
        $sql .= ' where 1 ';

        $para = array();//给para制空
        switch ($mystatus) {
            case ''://为空时显示所有
                break;
            case 'unline':
                $sql .= ' and device.mystatus is null ';//取数据库里为null的所有值  注：不能用=
                break;
            default:
                $sql .= ' and device.mystatus =:mystatus ';
                $para[':mystatus'] = $mystatus;
                break;
        }



        /* ic不为空时 加条件提数据 */
        if ('' !== $ic) {
            $sql .= ' and device.ic like :ic';
            $para[':ic'] = '%' . $ic . '%';
        }
         if( ''!=$comtitle)
        {
              $sql.= ' and com.title like "%'.$comtitle.'%" '; 
        }

        $sql .= ' order by id desc ';

        $result = $this->main->exers($sql, $para);



        $this->j['list'] = $result;








        if (!$this->ckerr()) {
            return false;
        }
    }

    /* 保存用户 */

    function esave() {
        $we = & $GLOBALS['main'];

        $id = $we->rfid();

        $we->posttype = 'post';


        $rs['ic'] = $we->request('ic', '设备ic', 2, 20, 'char', 'invalid');


        $this->ckerr();

        $deviceic = $rs['ic']; //更新门的ic时用

        /* 检测重复ic */
        if ($we->hasic(sh . '_device', $id, $rs['ic'])) {
            $this->ckerr('设备ic重复', 'ic');
        };

       $this->pdo->update(sh . '_device', $rs, 'id=:id', Array(':id' => $id));   

        /* 更新门的ic */
        unset($rs);
        $rs['deviceic'] = $deviceic;        
        $id = $this->pdo->update(sh . '_door', $rs, 'deviceid=:id', Array(':id' => $id));
        $this->j['success'] = 'y';
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
            $this->ckerr('已经有这个设备ic了,请重新输入');
        }

        /* 添加设备 */
        $rs['comid'] = 0;
        $rs['doornum'] = $this->devicetype[$rs['typeic']]['doornum'];
        $rs['goodsnum'] = 0;
        $rs['isrun'] = 0;

        $rs['stimeint'] = time();

        $id = $this->pdo->insert(sh . '_device', $rs);

        /* 插入门的表 */
        $typeic = $rs['typeic'];
        $deviceid = $id;
        $deviceic = $rs['ic'];

        unset($rs);
        for ($i = 0; $i < $this->devicetype[$typeic]['doornum']; $i++) {
            $rs['title'] = $i + 1;
            $rs['hasgoods'] = 0;
            $rs['mystatus'] = 'running';
            $rs['doorstatus'] = 'close';

            $rs['deviceid'] = $deviceid;
            $rs['deviceic'] = $deviceic;

            $rs['goodsic'] = '';
            $rs['goodsid'] = 0;

            $rs['comid'] = 0;
            $id = $this->pdo->insert(sh . '_door', $rs);
        }

        $this->j['success'] = 'y';
    }

    function dodel() {
        $id = $this->main->rqid();

        /* 提取店铺 */
        $sql = 'select * from `' . sh . '_device` where 1 ';
        $sql .= ' and id=:id';
        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        /* 检测还在店铺里了，不能删除 */
        if (0 != $result['comid']) {
            $this->ckerr('请先把设备移出店铺再删除');
        }

        /* 删除门 */
        $sql = 'delete from `' . sh . '_door` where 1 ';
        $sql .= ' and deviceid=:id;';

        /* 删除设备 */
        $sql .= 'delete from `' . sh . '_device` where 1 ';
        $sql .= ' and id=:id';

        $this->pdo->doSql($sql, Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    function dobelong() {


        $we = & $GLOBALS['main'];

        $id = $we->rid();

        $we->posttype = 'post';
        $rs['comid'] = $we->request('comid', '店铺ID', 0, 999999, 'int');
        $rs['comic'] = $we->request('comic', '店铺IC', 1, 20, 'char', 'invalid', false);

        $this->ckerr();


        /* 检查id,ic是不是变了 */
        $sql = 'select comid,comic from `' . sh . '_device` where 1 ';
        $sql .= ' and id=:deviceid';
        $result = $this->pdo->fetchOne($sql, Array(':deviceid' => $id));
        if (false == $result) {
            $this->ckerr('没找到这个设备');
        }
        if (($rs['comid'] == $result['comid']) And ( $rs['comic'] == $result['comic'])) {
            $this->ckerr('所属店铺和以前一样啊，请重新填写');
        }



        $comid = $rs['comid']; //下面更新门的comid用

        /* 检测是不是有这个店铺 */
        if (!($rs['comid'] == 0 and $rs['comic'] == '')) {
            $comid = $rs['comid']; //下面更新门的comid用

            /* 检测id,ic是否一至 */
            $sql = 'select count(*) from `' . sh . '_com` where 1 ';
            $sql .= ' and id=:comid ';
            $sql .= ' and ic=:comic ';

            $counts = $this->pdo->counts($sql, $rs);

            if ($counts == 0) {
                $this->ckerr('没找到这个店铺');
            }
        } else {
            $comid = 0;
        }

        /* if ($rs['comid'] == 0 and $rs['comic'] == '') { //移出店铺 */

        $rs['placeid'] = 0;
        $rs['placeic'] = '';
        $rs['goodsnum'] = 0;


        $this->pdo->update(sh . '_device', $rs, 'id=:id', Array(':id' => $id));


        /* 清除门里卖的商品 */
        unset($rs);

        $rs['hasgoods'] = 0;
        $rs['goodsic'] = '';
        $rs['goodsid'] = 0;
        $rs['comgoodsid'] = 0;
        $rs['placeid'] = 0;
        $rs['comid'] = $comid;



        $this->pdo->update(sh . '_door', $rs, 'deviceid=:id', Array(':id' => $id));


        $this->j['success'] = 'y';
    }

    /* 对用户的各种操作 */

    function doadmin() {
        $id = $this->main->rqid();

        /* 提店铺信息 */
        $sql = 'select * from `' . sh . '_device` where 1 ';
        $sql .= ' and id=:id ';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        /* 检测还没分配酒店了，不能运行 */
        if (0 == $result['comid']) {
            $this->ckerr('还没分配给店铺了，不能修改状态');
        }

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

    /* 取店铺信息 */

    function getdata() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_device` where 1 ';
        $sql .= ' and id=:id';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $this->j['data'] = $result;
    }

}

$admin_business_device = new admin_business_device(); //建立类的实例
unset($admin_business_device); //释放类占用的资源
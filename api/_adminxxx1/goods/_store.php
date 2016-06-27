<?php

/* 商品库存 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once AdminApiPath . '_main.php';


/*
 * mytype:出入库类型 
 * in : 入库
 * out : 出库
 * delivery ： 发货
 *  */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
        // print_r($this->act);die;
        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
            case 'doin':
                $this->doin();
                $this->output();
                break;
            case 'doout':
                $this->doout();
                $this->output();
                break;
            case 'dodelivery':
                $this->dodelivery();
                $this->output();
                break;
            case 'history':
                $this->history();
                $this->output();
                break;
            case 'alarm':
                $this->formalarm();
                $this->output();
                break;
            case 'savealarm':
                $this->savealarm();
                $this->output();
                break;
        }
    }

    function pagemain() {
        /* 接收参数 */
        $this->posttype = 'get';
        $statusjb = $this->main->request("statusjb", "状态", 0, 255, 'char');
        $title = $this->main->request('title', '商品名称', 1, 50, 'char', '', false);
        $ic = $this->main->request('ic', '商品ic', 1, 50, 'char', '', false);
        $this->j['search']['title'] = $title;
        $this->j['search']['statusjb'] = $statusjb;
        $this->j['search']['ic'] = $ic;
        $para = Array();
        /* 提取所有商品 */
        $sql = 'select * from `' . sh . '_goods` where 1 ';
        $sql .= ' and isgroup=0 ';

        //print_r($statusjb);die;
        if ('正常' == $statusjb) {
            $sql .= ' and inventories>=inventoriesalarm';
        } else if ('报警' == $statusjb) {
            $sql .= ' and inventories<inventoriesalarm';
        }
        if ('' != $title) {

            $sql .= ' and title like :title';
            $para[':title'] = '%' . $title . '%';
        }
        if ('' != $ic) {
            $sql .= ' and ic ="' . $ic . '"';
        }

        $sql .= ' order by id desc ';

        $a_goods = $this->main->exers($sql, $para);

        /* 提取所有单品，供显示在组合品里 */
        $sql = 'select id,title,preimg from `' . sh . '_goods` where 1 ';
        $sql .= ' and isgroup=0 ';
        $result = $this->pdo->fetchAll($sql);

        /* 单口以id做为索引 */
        $a_singlegoods = array();
        foreach ($result as $v) {
            $a_singlegoods[$v['id']] = $v;
        }

        $this->j['list'] = $a_goods;
        $this->j['listsingle'] = & $a_singlegoods;
    }

    function doin() {
        $main = & $GLOBALS['main'];

        $goodsid = $main->rqid('goodsid');

        $main->posttype = 'post';

        $formcode = $main->request('formcode', '凭证号', 1, 20, 'char', 'invalid');
        $mycount = $main->request('mycount', '数量', 1, 99999, 'int');

        if (!$this->ckerr()) {
            return false;
        }

        /* 检测凭证号不能重复,不检测了,一批来几个货时批号一样 */
        //if ($main->hasname(sh . '_store', 'formcode', $formcode)) {
        //    $this->ckerr('凭证号重复');
        //    return false;
        //}

        /* ==============================
         * 事务处理
         */
        $pdo = & $GLOBALS['pdo'];

        try {
            $pdo->begintrans();

            $currenttime = time();

            /* 添加入库记录 */
            $rs['goodsid'] = $goodsid;
            $rs['formcode'] = $formcode;

            $rs['mycount'] = $mycount;
            $rs['mytype'] = 'in';
            $rs['other'] = '';

            $rs['comid'] = 0;
            $rs['comic'] = 0;
            $rs['comname'] = '';


            $rs['duid'] = $this->main->user['id'];
            $rs['dname'] = $this->main->user['u_fullname'];
            $rs['stime'] = date('Y-m-d H:i:s');
            $rs['stimeint'] = $currenttime;

            $storeid = $this->pdo->insert(sh . '_store', $rs);

            /* 更新统计 */
            $sql = 'update `' . sh . '_goods` set ';
            $sql .= ' inventories=inventories+' . $mycount;
            $sql .= ' ,inventoriessum=inventoriessum+' . $mycount;
            $sql .= ' where id=' . $goodsid;

            $this->pdo->doSql($sql);

            $pdo->submittrans();
        } catch (PDOException $e) {

            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['success'] = 'y';
    }

    /* 出库 */

    function doout() {
        $main = & $GLOBALS['main'];

        $goodsid = $main->rqid('goodsid');

        $main->posttype = 'post';

        $formcode = $main->request('formcode', '凭证号', 1, 20, 'char', 'invalid');
        $mycount = $main->request('mycount', '数量', 1, 99999, 'int');
        $other = $main->request('other', '原因', 1, 50, 'char', 'encode');

        if (!$this->ckerr()) {
            return false;
        }

        /* 检测剩余数量够不够 */
        $sql = 'select inventories from `' . sh . '_goods` where id=:goodsid';
        $result = $this->pdo->fetchOne($sql, Array(':goodsid' => $goodsid));
        if (false == $result) {
            $this->ckerr(1018);
            return;
        }
        if ($mycount > $result['inventories']) {
            $this->ckerr('出库数大于剩余数量了,请重新填写出库数量');
            return;
        }

        /* 检测凭证号不能重复,不检测了,一批来几个货时批号一样 */
        //if ($main->hasname(sh . '_store', 'formcode', $formcode)) {
        //    $this->ckerr('凭证号重复');
        //    return false;
        //}

        /* ==============================
         * 事务处理
         */
        $pdo = & $GLOBALS['pdo'];

        try {
            $pdo->begintrans();

            $currenttime = time();

            /* 添加出库记录 */
            $rs['goodsid'] = $goodsid;
            $rs['formcode'] = $formcode;

            $rs['mycount'] = $mycount;
            $rs['mytype'] = 'out';
            $rs['other'] = $other;

            $rs['comid'] = 0;
            $rs['comic'] = 0;
            $rs['comname'] = '';


            $rs['duid'] = $this->main->user['id'];
            $rs['dname'] = $this->main->user['u_fullname'];
            $rs['stime'] = date('Y-m-d H:i:s');
            $rs['stimeint'] = $currenttime;

            $storeid = $this->pdo->insert(sh . '_store', $rs);

            /* 更新统计 */
            $sql = 'update `' . sh . '_goods` set ';
            $sql .= ' inventories=inventories-' . $mycount;
            $sql .= ' ,inventoriessum=inventoriessum-' . $mycount;
            $sql .= ' where id=' . $goodsid;

            $this->pdo->doSql($sql);

            $pdo->submittrans();
        } catch (PDOException $e) {

            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['success'] = 'y';
    }

    /* 发货 */

    function dodelivery() {
        $main = & $GLOBALS['main'];

        $goodsid = $main->rqid('goodsid');

        $main->posttype = 'post';

        $formcode = $main->request('formcode', '凭证号', 1, 20, 'char', 'invalid');
        $mycount = $main->request('mycount', '数量', 1, 99999, 'int');

        $comid = $main->request('comid', '店铺id', 1, 999999, 'int');
        $comic = $main->request('comic', '店铺ic', 1, 20, 'char', 'invalid');

        if (!$this->ckerr()) {
            return false;
        }

        /* 检测店铺id,ic是否正确，并取回店铺名称 */
        $sql = 'select title from `' . sh . '_com` where 1 ';
        $sql .= ' and id=:comid';
        $sql .= ' and ic=:comic';
        unset($para);
        $para[':comid'] = $comid;
        $para[':comic'] = $comic;
        $result = $this->pdo->fetchOne($sql, $para);
        if (false === $result) {
            $this->ckerr('没找到这个店铺');
            return false;
        } else {
            $comname = $result['title'];
        }

        /* 检测店铺是不是卖这种商品 */
        $sql = 'select id from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and goodsid=:goodsid';
        $sql .= ' and comid=:comid';
        unset($para);
        $para[':goodsid'] = $goodsid;
        $para[':comid'] = $comid;
        $result = $this->pdo->fetchOne($sql, $para);
        if (false == $result) {
            $this->ckerr('这个店铺不卖这种商品');
            return;
        } else {
            $comgoodsid = $result['id'];
        }

        /* 检测剩余数量够不够 */
        $sql = 'select inventories from `' . sh . '_goods` where id=:goodsid';
        $result = $this->pdo->fetchOne($sql, Array(':goodsid' => $goodsid));
        if (false == $result) {
            $this->ckerr(1018);
            return;
        }
        if ($mycount > $result['inventories']) {
            $this->ckerr('发货数大于剩余数量了,请重新填写发货数量');
            return;
        }

        /* 检测凭证号不能重复,不检测了,一批来几个货时批号一样 */
        //if ($main->hasname(sh . '_store', 'formcode', $formcode)) {
        //    $this->ckerr('凭证号重复');
        //    return false;
        //}

        /* ==============================
         * 事务处理
         */
        $pdo = & $GLOBALS['pdo'];

        try {
            $pdo->begintrans();

            $currenttime = time();

            /* 添加出库记录 */
            $rs['goodsid'] = $goodsid;
            $rs['formcode'] = $formcode;

            $rs['mycount'] = $mycount;
            $rs['mytype'] = 'delivery';
            $rs['other'] = '';

            $rs['comid'] = $comid;
            $rs['comic'] = $comic;
            $rs['comname'] = $comname;


            $rs['duid'] = $this->main->user['id'];
            $rs['dname'] = $this->main->user['u_fullname'];
            $rs['stime'] = date('Y-m-d H:i:s');
            $rs['stimeint'] = $currenttime;

            $storeid = $this->pdo->insert(sh . '_store', $rs);

            /* 更新统计 */
            $sql = 'update `' . sh . '_goods` set ';
            $sql .= ' inventories=inventories-' . $mycount;
            $sql .= ' where id=' . $goodsid;
            $this->pdo->doSql($sql);

            /* 更新店铺商品统计 */
            $sql = 'update `' . sh . '_comgoods` set ';
            $sql .= ' inventories=inventories+' . $mycount;
            $sql .= ' where id=' . $comgoodsid;

            $this->pdo->doSql($sql);

            /* 添加店铺仓库记录, 对店铺是入库 */
            unset($rs);
            $rs['goodsid'] = $goodsid;
            $rs['comgoodsid'] = $comgoodsid;
            $rs['formcode'] = $formcode;
            $rs['mycount'] = $mycount;
            $rs['mytype'] = 'in'; //
            $rs['comid'] = $comid;


            $rs['duid'] = $this->main->user['id'];
            $rs['dname'] = $this->main->user['u_fullname'];
            $rs['stime'] = date('Y-m-d H:i:s');
            $rs['stimeint'] = $currenttime;

            $this->pdo->insert(sh . '_comstore', $rs);

            $pdo->submittrans();
        } catch (PDOException $e) {

            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['success'] = 'y';
    }

    function history() {
//        print_r('11');die;
        $this->posttype = 'get';
        $mytype = $this->main->request("mytype", "状态", 0, 255, 'char');
        $title = $this->main->request('title', '商品名称', 1, 50, 'char', '', false);
        $ic = $this->main->request('ic', '商品ic', 1, 50, 'char', '', false);
        /* 返回前端 */
        $this->j['search']['title'] = $title;
        $this->j['search']['mytype'] = $mytype;
        $this->j['search']['ic'] = $ic;
        $para = Array();
        /* 处理参数 */
        if ('' == $mytype) {
            $mytype = 'all';
        }

        $sql = 'select s.* ';
        $sql .= ' ,g.preimg as preimg ';
        $sql .= ' ,g.ic as ic';
        $sql .= ' ,g.title as title ';
        
        $sql .= ' from `' . sh . '_store` as s ';
        $sql .= ' left join `' . sh . '_goods` as g on s.goodsid=g.id ';
        $sql .= ' where 1';
        /* 为all时 加条件提数据 */
        if ('all' !== $mytype) {
            $sql .= ' and s.mytype =:mytype ';
            $para[':mytype'] = $mytype;
        }
        $this->j['search']['mytype'] = $mytype;
        if ('' != $title) {

            $sql .= ' and title like :title';
            $para[':title'] = '%' . $title . '%';
        }
        if ('' != $ic) {
            $sql .= ' and ic ="' . $ic . '"';
        }

        $sql .= ' order by s.id desc ';
//        print_r($sql);die;

        $result = $this->main->exers($sql, $para);

        $this->j['list'] = $result;
    }

    function formalarm() {
        $goodsid = $this->main->rqid('id');

        $data = $this->getgoods($goodsid);

        $this->j['data'] = $data;
    }

    function savealarm() {
        $goodsid = $this->main->rqid('goodsid');

        $this->main->posttype = 'post';
        $inventoriesalarm = $this->main->request('inventoriesalarm', '警戒库存', 0, 9999999, 'int');

        if (!$this->ckerr()) {
            return false;
        }
        $sql = 'update `' . sh . '_goods` set inventoriesalarm=' . $inventoriesalarm;
        $sql .= ' where id=' . $goodsid;

        $this->pdo->doSql($sql);

        $this->j['success'] = 'y';
    }

    function getgoods($goodsid) {
        $sql = 'select * from `' . sh . '_goods` where 1 ';
        $sql .= ' and id=:goodsid ';

        return $this->pdo->fetchOne($sql, Array(':goodsid' => $goodsid));
    }

}

$myapi = new myapi();
unset($sys_admin_user);

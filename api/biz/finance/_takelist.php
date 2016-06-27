<?php

require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';

require_once ApiPath . 'biz/_main.php'; //公共

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain(); //权限

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        $jsonact = array('json'
            , 'nsave'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        switch ($this->act) {
            case '':
                $this->thismain();
                $this->output();
                break;

            case 'export':
                $this->thismain();
                $this->output();
                break;

            case 'take':
                $this->myform();
                $this->output();
                break;

            /* 以下返回json */
            case 'nsave':
                $this->saveform();
                $this->output();
                break;

            case 'viewicantake':
                $this->viewicantake();
                $this->output();
                break;

            case 'history':
                $this->history();
                $this->output();
                break;

            default:
                break;
        }
    }

    function thismain() {
        $c_money = new cls_money;
        $takestatus = $c_money->takestatus;

        $this->posttype = 'get';
        $comic = $this->main->request('comic', '店铺编码', 1, 50, 'char', '', false);
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);
        $mystatus = $this->main->request('mystatus', '申请状态', 1, 50, 'char', 'invalid', false);

        /* 传回前端 */
        $this->j['search']['comic'] = $comic;
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['comname'] = '';
        $this->j['search']['mystatus'] = $mystatus;

        $this->j['account']['doingtake'] = 0;
        $this->j['account']['acanuse'] = 0;

        /* 提取可提款总额 */
        $sql = 'select acanuse from `' . sh . '_account` where 1 ';
        $sql .= ' and mytype="biz" ';
        $sql .= ' and comid=:comid';

        unset($para);
        $para[':comid'] = $this->main->user['comid'];

        $result = $this->pdo->fetchOne($sql, $para);
        $this->j['account']['acanuse'] = $result['acanuse'];

        /* check date */

        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }
        if (!$this->ckerr()) {
            return false;
        }

        /* check date */

        if ('' == $date1) {
            $date1_int = strtotime(date('Y-m-d', (time() - 7 * 24 * 3600)));
        } else {
            $date1_int = strtotime($date1);
        }

        if ('' == $date2) {
            $date2_int = strtotime(date('Y-m-d', time()));
        } else {
            $date2_int = strtotime($date2);
        }


        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);
        /* 处理参数 */
        if ('' == $mystatus) {
            $mystatus = 'all';
        }

        $this->j['search']['mystatus'] = $mystatus;

        $sql = 'select * from `' . sh . '_takemoney` as money';
        $sql .= ' where 1 ';


        /* 为all时 加条件提数据 */

        $sql .= ' and comid=:comid';
        $para[':comid'] = $this->main->user['comid'];
        if ('all' !== $mystatus) {
            $sql .= ' and money.mystatus =:mystatus ';
            $para[':mystatus'] = $mystatus;
        }


        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);


        $sql .= ' order by id desc ';
        if ($this->act == '') {
            $result = $this->main->exers($sql, $para);
        } else {
            $result = $this->pdo->fetchAll($sql, $para, true);
            //$result['rs']=$result;
        }
        // print_r($result['rs']);die;
        /* 处理状态名称 */
        $i = 0;
        foreach ($result['rs'] as $v) {
            if (array_key_exists($v['mystatus'], $takestatus)) {
                $result['rs'][$i]['mystatustitle'] = $takestatus[$v['mystatus']];
            } else {
                $result['rs'][$i]['mystatustitle'] = '';
            }

            $i++;
        }

        $this->j['list'] = $result;
        //print_r($result);die;





        /* 统计--提款中 */
        $sql = 'select sum(myvalue) as myvalue from `' . sh . '_takemoney` where 1 ';
        $sql .= ' and (mystatus="new" OR mystatus="checked")';
        $sql .= ' and comid=:comid';

        unset($para);
        $para[':comid'] = $this->main->user['comid'];


        $result = $this->pdo->fetchOne($sql, $para);
        $this->j['account']['doingtake'] = $result['myvalue'];
    }

    function myform() {
        /* 提取可提款总额 */
        $sql = 'select * ';
        //$sql .= ' ,account.acanuse as acanuse';
        $sql .= ' from `' . sh . '_com` ';
        //$sql .= ' left join `' . sh . '_account` as account  ';
        //$sql .= ' on account.comid=com.id ';
        $sql .= ' where 1 and id= :comid ';
        $result = $this->pdo->fetchOne($sql, Array(':comid' => $this->main->user['comid']));


        /* 结点 - 店铺信息 */
        $this->j['list'] = $result;

        /* ### 可提款,取未提款总额 */
        $sql = 'select sum(myvalue) as myvalue, count(*) as mycount ';
        $sql .= ' from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and myvalue>0 '; //只显示入款
        $sql .= ' and paymentstatus=0 '; //还没申请提现的
        $sql .= ' and mytype in(3010,3020) '; //充值和利润可提

        /* 加上所属店铺 */
        $sql .= ' and comid=:comid';
        $para[':comid'] = $this->main->user['comid'];

        $result = $this->pdo->fetchOne($sql, $para);

        $this->j['take'] = $result;


        /* 可提款idlist */
        $sql = 'select id from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and myvalue>0 '; //只显示入款
        $sql .= ' and paymentstatus=0 '; //还没申请提现的

        /* 加上所属店铺 */
        $sql .= ' and comid=:comid';
        $para[':comid'] = $this->main->user['comid'];

        $result = $this->pdo->fetchAll($sql, $para);
        $idlist = array_column($result, 'id');

        /* 结点 */
        $this->j['idlist'] = join(',', $idlist);
    }

    function history() {
        $pid = $this->main->rqid('pid');

        /* 提取这条提现申请 */
        $sql = 'select * from `' . sh . '_takemoney` where 1 ';
        $sql .= ' and comid=:comid ';
        $sql .= ' and id=:pid ';

        $para[':comid'] = $this->main->user['comid'];
        $para[':pid'] = $pid;

        $result = $this->pdo->fetchAll($sql, $para);

        if (false === $result) {
            $this->ckerr('没找到这条提现记录');
            return;
        }
        $idlist = join(',', array_column($result, 'moneycomidlist'));

        unset($para);

        /* 商店提现相应财务记录 */
        $sql = 'select m.*, o.mygoods as mygoods from `' . sh . '_moneycom` as m ';
        $sql .= ' left join `' . sh . '_order` as o on m.orderid=o.id ';
        $sql .= ' where 1 ';
        $sql .= ' and m.myvalue>0 '; //只显示入款
        //$sql .= ' and paymentstatus <> 0 '; //还没申请提现的
        $sql .= ' and m.mytype in(3010,3020) ';

        /* 加上所属店铺 */
        $sql .= ' and m.comid=:comid ';
        $sql .= ' and m.id in (' . $idlist . ') ';
        $sql .= ' order by m.id desc ';

        $para[':comid'] = $this->main->user['comid'];


        $result = $this->main->exers($sql, $para);

        $this->j['list'] = $result;
    }

    function saveform() {
        //echo pr();
        $c_money = new cls_money;



        $this->main->posttype = 'post';

        $idlist = $this->main->rqidlist('idlist', 'post');
        $myvalue = $this->main->rfid('myvalue');

        if ('' == $idlist) {
            $this->ckerr('没有可提现记录');
            return;
        }

        /* 提取 */
        $sql = 'select sum(myvalue) as myvalue from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and myvalue>0 '; //只显示入款
        $sql .= ' and paymentstatus="0" '; //还没申请提现的
        $sql .= ' and mytype in(3010,3020) ';

        /* 加上所属店铺 */
        $sql .= ' and comid=:comid';
        $sql .= ' and id in(' . $idlist . ')';

        $para[':comid'] = $this->main->user['comid'];

        $result = $this->pdo->fetchOne($sql, $para);

        if (false === $result OR 0 == $result['myvalue']) {
            $this->ckerr('没有可提现记录');
            return;
        } else {
            if ($myvalue != $result['myvalue']) {
                $this->ckerr('可提现金额错误');
                return;
            }
        }

        /*检查是否完善了账户信息*/
        if(''==$this->main->company['a_number'] OR ''==$this->main->company['a_name'] OR ''==$this->main->company['a_bank']){
            $this->ckerr('请联系管理员完善收款账户再行提交');
            return false;
        }

        /* 添加提现记录并扣款 */
        $take['uid'] = $this->main->user['id'];
        $take['comid'] = $this->main->user['comid'];
        $take['u_gic'] = $this->main->user['u_gic'];
        $take['myvalue'] = $myvalue;
        $take['fullname'] = $this->main->user['u_fullname'];
        $take['payaccount'] = $this->main->company['a_number'];
        $take['payname'] = $this->main->company['a_name'];
        $take['paybank'] = $this->main->company['a_bank'];
        $take['other'] = '';
        $take['moneycomidlist'] = $idlist;
//    print_r($take);die;
        /* 扣款 */
        $money['myway'] = 'alipay';
        $money['title'] = '提现';
        $money['mywayic'] = '';
        $money['amoun'] = $myvalue;
        $money['formcode'] = '';
        $money['formdate'] = null;
        $money['uid'] = $this->main->user['id'];
        $money['orderid'] = 0;
        $money['comid'] = $this->main->user['comid'];
        $money['other'] = '';



        //下面这几个跟据入同账本有变化
        $money['action'] = 'substract';
        $money['mytype'] = 4020;
        $money['acceptgroup'] = 'com';

        /* 事务处理 */

        $pdo = & $GLOBALS['pdo'];
        try {
            $pdo->begintrans();

            $c_money->addtakemoney($take);
            if (!$c_money->domoney($money)) {
                $this->ckerr();
                return;
            }

            /* 更新款项记录状态 */
            $sql = 'update `' . sh . '_moneycom` set paymentstatus=1 where 1 ';
            $sql .= ' and myvalue>0 '; //只显示入款
            $sql .= ' and paymentstatus="0" '; //还没申请提现的

            /* 加上所属店铺 */
            $sql .= ' and comid=:comid';
            $sql .= ' and id in(' . $idlist . ')';

            $para[':comid'] = $this->main->user['comid'];

            $this->pdo->doSql($sql, $para);

            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['success'] = 'y';
        $this->j['msg'] = '保存成功';
    }

    function viewicantake() {
        /* 提取商家财务记录 */

        $sql = 'select m.*,o.mygoods as mygoods from `' . sh . '_moneycom` as m ';
        $sql .= ' left join `' . sh . '_order` as o on m.orderid=o.id ';
        $sql .= ' where 1 ';
        $sql .= ' and m.myvalue>0 '; //只显示入款
        $sql .= ' and m.paymentstatus=0 '; //还没申请提现的
        $sql .= ' and m.mytype in(3010,3020) ';

        /* 加上所属店铺 */
        $sql .= ' and m.comid=:comid';
        $para[':comid'] = $this->main->user['comid'];


        $sql .= ' order by id desc ';

        $result = $this->main->exers($sql, $para);



        $this->j['list'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

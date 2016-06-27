<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
require_once ApiPath . '_adminxxx1/_main.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
            case 'dopass':
                $this->dopass();
                $this->output();
                break;
            case 'paid':
                $this->formpaid();
                $this->output();
                break;
            case 'dopaid': //更新为已打款，并保存流水号
                $this->dopaid();
                $this->output();
                break;
            case 'nsave': //审核失败
                $this->savenew();
                $this->output();
                break;
            case 'history':
                $this->history();
                $this->output();
                break;
            case 'detail':
                $this->detail();
                $this->output();
                break;
            case 'exportdetail':
                $this->detail();
                $this->output();
                break;
        }
    }

    function pagemain() {
        $c_money = new cls_money();

        /* 接收参数 */
        $this->main->posttype = 'get';
        $mystatus = $this->main->request('mystatus', '状态', 1, 10, 'char', 'invalid', false);
        $comic = $this->main->request('comic', '店铺编码', 1, 50, 'char', '', false);
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);
//        $mystatus = $this->main->request('mystatus', '申请状态', 1, 50, 'char', 'invalid', false);
        /* 传回前端 */
        $this->j['search']['comic'] = $comic;
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['mystatus'] = $mystatus;
        if (!$this->ckerr()) {
            return false;
        }

        /* check com */
        if ('' != $comic) {
            $sql = 'select id,title from `' . sh . '_com` where ic=:comic';
            $result = $this->pdo->fetchOne($sql, Array(':comic' => $comic));
            if (false == $result) {
                $this->ckerr('没找到这个店铺');
                return false;
            } else {
                $a_com = $result;
                $this->j['search']['comname'] = $result['title'];
            }
        } else {
            $this->j['search']['comname'] = '';
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

        /* check date */
        if ($date1_int > $date2_int) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }

        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);


        /* 处理参数 */
        if ('' == $mystatus) {
            $mystatus = 'all';
        }

        $this->j['search']['mystatus'] = $mystatus;


        /* 提现记录 */
        $sql = 'select money.*,com.title as comname,com.telfront as telfront from `' . sh . '_takemoney` as money ';
        $sql .= ' left join `' . sh . '_com` as com on money.comid=com.id ';
        $sql .= ' where 1 ';

        /* 为all时 加条件提数据 */
        if ('all' !== $mystatus) {
            $sql .= ' and money.mystatus =:mystatus ';
            $para[':mystatus'] = $mystatus;
        }

        /* date */
        $sql .= ' and money.stimeint>=:date1_int';
        $sql .= ' and money.stimeint<=:date2_int';

        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);
        /* 搜店铺 */
        if ('' != $comic) {
            $sql.=' and  money.comid=:comid';
            $para[':comid'] = $a_com['id'];
        }



        $sql .= ' order by money.id desc ';


        $result = $this->main->exers($sql, $para);

        $i = 0;
        foreach ($result['rs'] as $v) {
            $result['rs'][$i]['mystatusname'] = $c_money->takestatus[$v['mystatus']];
            $i++;
        }

        $this->j['list'] = $result;

        /* 提取统计 */
        unset($para);
        $sql = 'select count(*) as mycount,sum(myvalue) as mysum, mystatus from `' . sh . '_takemoney` where 1 ';
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);

        /* 搜店铺 */
        if ('' != $comic) {
            $sql.=' and  comid=:comid';
            $para[':comid'] = $a_com['id'];
        }


        $sql .= ' group by mystatus ';
        $result = $this->pdo->fetchAll($sql, $para);

        /* 用状态做索引 */
        $a = array();
        foreach ($result as $v) {
            $a[$v['mystatus']] = $v;
            $a[$v['mystatus']]['mystatusname'] = $c_money->takestatus[$v['mystatus']];
        }

        $this->j['account'] = $a;



//        /* 统计待审核 */
//        $sql = 'select count(*) as mycount,sum(myvalue) as mysum from `' . sh . '_takemoney` where 1 ';
//        $sql .= ' and mystatus="new" ';
//        $result = $this->pdo->fetchOne($sql);
//
//        if (false === $result) {
//            $this->j['account']['countnew'] = 0;
//            $this->j['account']['sumnew'] = 0;
//        } else {
//            $this->j['account']['countnew'] = $result['mycount'];
//            $this->j['account']['sumnew'] = $result['mysum'];
//        }
//
//        /* 统计待打款 */
//        $sql = 'select count(*) as mycount,sum(myvalue) as mysum from `' . sh . '_takemoney` where 1 ';
//        $sql .= ' and mystatus="checked" ';
//        $result = $this->pdo->fetchOne($sql);
//
//        if (false === $result) {
//            $this->j['account']['countchecked'] = 0;
//            $this->j['account']['sumchecked'] = 0;
//        } else {
//            $this->j['account']['countchecked'] = $result['mycount'];
//            $this->j['account']['sumchecked'] = $result['mysum'];
//        }
    }

    function dopass() {
        $id = $this->main->rqid('id');

        $sql = 'update `' . sh . '_takemoney` set mystatus="checked" where 1 ';
        $sql .= ' and mystatus="new" ';
        $sql .= ' and id=' . $id;

        $result = $this->pdo->doSql($sql);

        if ($result > 0) {
            $this->j['success'] = 'y';
        } else {
            $this->ckerr('操作失败');
            return;
        }
    }

    /* 已打款 */

    function formpaid() {
        
    }

    function dopaid() {
        $c_money = new cls_money();

        $id = $this->main->rqid('id');

        $main = & $GLOBALS['main'];
        $main->posttype = 'post';
        $formcode = $main->request('formcode', '凭证号', 2, 50, 'char');
        $formdate = $main->request('formdate', '凭证日期', 2, 20, 'date');

        if (!$this->ckerr()) {
            return false;
        }

        /* 提取这笔提现记录 */
        $a_take = $c_money->gettakemoneybyid($id);
        
        if(false==$a_take){
            $this->ckerr('没找到这笔提现记录');
            return false;
        }
        
        $moneycomidlist = $a_take['moneycomidlist'];
        
        if(''==$moneycomidlist){
            $this->ckerr('没找到相应财务记录');
            return false;
        }

        $sql = 'update `' . sh . '_takemoney` set mystatus="done" where 1 ';
        $sql .= ' and mystatus="checked" ';
        $sql .= ' and id=' . $id;

        $result = $this->pdo->doSql($sql);

        if ($result > 0) {
            $this->j['success'] = 'y';
        } else {
            $this->checkerr('操作失败');
        }

        /* 减平台财务 */
        /* ==============================
         */
        $money['title'] = '提现';
        $money['mywayic'] = 'alipay';
        $money['amoun'] = $a_take['myvalue'];
        $money['formcode'] = $formcode;
        $money['formdate'] = strtotime($formdate);
        $money['uid'] = $a_take['uid'];
        $money['orderid'] = 0;
        $money['comid'] = $a_take['comid'];
        $money['other'] = '提现ID:' . $id;

        $money['action'] = 'substract';
        $money['mytype'] = 6010;
        $money['acceptgroup'] = 'plat';

        /*
         * 事务处理
         */
        $pdo = & $this->pdo;
        try {
            $pdo->begintrans();
            
            /*更新财务记录为已提现*/
            $sql = 'update `'.sh.'_moneycom` set paymentstatus=3 where id in('.$moneycomidlist.')';
            $this->pdo->doSql($sql);

            if ($c_money->domoney($money)) {
                $this->j['success'] = 'y';
            } else {
                $this->ckerr();
                return false;
            }

            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }
    }

    function thismain_() {
        $this->posttype = 'post';
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
        if (!$this->ckerr()) {
            return false;
        }

        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }
        /* check com */
        if ('' != $comic) {
            $sql = 'select id,title from `' . sh . '_com` where ic=:comic';
            $result = $this->pdo->fetchOne($sql, Array(':comic' => $comic));
            if (false == $result) {
                $this->ckerr('没找到这个店铺');
                return false;
            } else {
                $a_com = $result;
                $this->j['search']['comname'] = $result['title'];
            }
        } else {
            $this->j['search']['comname'] = '';
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

        $sql = 'select * from `' . sh . '_moneyplat` where 1 ';

        /* 搜店铺 */
        if ('' != $comic) {
            $sql .= ' and comid=:comid';
            $para[':comid'] = $a_com['id'];
        }

        /* date */
        $sql .= ' and stimeint>=:date1_int';
        $sql .= ' and stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);


        $sql .= ' order by id desc ';

        $result = $this->main->exers($sql, $para);

        $this->j['list'] = $result;






        /* 统计 */
        $sql = 'select count(*),sum(myvalue) from `' . sh . '_takemoney` where 1 ';
        $sql .= ' and mystatus=new ';
        $result = $this->fetchAll($sql);

        if (false === $result) {
            
        }



        /* 搜店铺 */
        //if ('' != $comic) {
        //    $sql .= ' and comid=:comid';
        //     $para[':comid'] = $a_com['id'];
        //}
//        /* date */
//        $sql .= ' and stimeint>=:date1_int';
//        $sql .= ' and stimeint<=:date2_int';
//        $para[':date1_int'] = $date1_int;
//        $para[':date2_int'] = $date2_int + (24 * 3600);
//
//        $result = $this->pdo->fetchOne($sql, $para);
//        $this->j['account'] = $result;
//
//
//
//        $this->j['account'] = $account;
    }

    /*
     * 审核未通过，钱退回原账户
     *      */

    function savenew() {
        $c_money = new cls_money();

        $id = $this->main->rqid();

        $this->main->posttype = 'post';
        $other = $this->main->request('other', '原因', 1, 255, 'char', 'encode');

        /*  if ($other == '') {
          $GLOBALS['errmsg'][] = '请填写未通过原因';
          // printf($GLOBALS['errmsg'][]);die;
          } */
        if (!$this->ckerr()) {
            return;
        }

        $a_take = $c_money->gettakemoneybyid($id);
        // print_r($a_take);die;
        $rs['mystatus'] = 'unchecked';
        $rs['other'] = $other;

        /* 更新为未审核通过，退回款项，并保存未通过原因 */

        /* ==============================
         * 事务处理
         */
        $pdo = & $GLOBALS['pdo'];
        try {
            $pdo->begintrans();

            $pdo->update(sh . '_takemoney', $rs, 'id=' . $id);


            /* 更新财务记录 paymentstatus回0 */
            $sql = 'update `' . sh . '_moneycom` set paymentstatus=0 where 1 ';
            $sql .= ' and id in(' . $a_take['moneycomidlist'] . ')';
            $this->pdo->doSql($sql);

            $money['title'] = '提现未通过';
            $money['mywayic'] = '';
            $money['amoun'] = $a_take['myvalue'];
            $money['formcode'] = '';
            $money['formdate'] = time();
            $money['uid'] = $a_take['uid'];
            $money['orderid'] = 0;
            $money['comid'] = $a_take['comid'];
            $money['other'] = $other;

            //$money['duid']
            //money['dnick']
            //下面这几个跟据不同账本有变化
            $money['action'] = 'add';
            $money['mytype'] = '3030';
            $money['acceptgroup'] = 'com';

            $result = $c_money->domoney($money);

            if ($result) {
                $this->j['success'] = 'y';
            } else {
                $this->ckerr('处理失败！');
            }
            $pdo->submittrans();
        } catch (PDOException $e) {
            $pdo->rollbacktrans();
            echo ($e);
            die();
        }
    }

    function history() {
        $this->posttype = 'post';
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

        if (!$this->ckerr()) {
            return false;
        }

        /* check date,没填默认7天 */
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

        if ($date1_int > $date2_int) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }

        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);

        /* check com */
        if ('' != $comic) {
            $sql = 'select id,title from `' . sh . '_com` where ic=:comic';
            $result = $this->pdo->fetchOne($sql, Array(':comic' => $comic));
            if (false == $result) {
                $this->ckerr('没找到这个店铺');
                return false;
            } else {
                $a_com = $result;
                $this->j['search']['comname'] = $result['title'];
            }
        } else {
            $this->j['search']['comname'] = '';
        }

        /* 提取数据 */
        $sql = 'select money.*, com.title as comname,com.telfront as telfront from `' . sh . '_takemoney` as money ';
        $sql .= ' left join `' . sh . '_com` as com on money.comid=com.id ';
        $sql .= ' where 1 ';
        $sql .= ' and (mystatus="uncheck" or mystatus="done" or mystatus="cancel") ';

        /* 搜店铺 */
        if ('' != $comic) {
            $sql .= ' and money.comid=:comid';
            $para[':comid'] = $a_com['id'];
        }

        /* date */
        $sql .= ' and money.stimeint>=:date1_int';
        $sql .= ' and money.stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);


        $sql .= ' order by money.id desc ';

        $result = $this->main->exers($sql, $para);

        $this->j['list'] = $result;
    }

    function detail() {
        $pid = $this->main->rqid('pid');

        /* 提取这条提现申请 */
        $sql = 'select * from `' . sh . '_takemoney` where 1 ';
        $sql .= ' and id=:pid ';

        $para[':pid'] = $pid;

        $result = $this->pdo->fetchAll($sql, $para);

        if (false === $result) {
            $this->ckerr('没找到这条提现记录');
            return;
        }
        $idlist = join(',', array_column($result, 'moneycomidlist'));

        if ('' == $idlist) {
            $idlist = 0;
        }

        unset($para);

        /* 商店提现相应财务记录 */
        $sql = 'select m.*, o.mygoods as mygoods,com.title as comname from `' . sh . '_moneycom` as m ';
        $sql .= ' left join `' . sh . '_order` as o on m.orderid=o.id ';
        $sql .= ' left join `' . sh . '_com` as com on m.comid=com.id ';
        $sql .= ' where 1 ';
        $sql .= ' and m.myvalue>0 '; //只显示入款
        //$sql .= ' and paymentstatus <> 0 '; //还没申请提现的
        $sql .= ' and m.mytype in(3010,3020) ';

        /* 加上所属店铺 */
        $sql .= ' and m.id in (' . $idlist . ') ';
        $sql .= ' order by m.id desc ';


        if ('detail' == $this->act) {
            $result = $this->main->exers($sql);
            $this->j['list'] = $result;
        } else {
            $result = $this->pdo->fetchAll($sql);

            $this->j['list'] = $result;
        }
    }

}

$myapi = new myapi();
unset($myapi);

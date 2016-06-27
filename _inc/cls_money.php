<?php

/* 财务处理 */

class cls_money {

    public $moneysetting;

    function __construct() {
        $this->pdo = & $GLOBALS['pdo'];
        $this->main = & $GLOBALS['main'];

        /* 财务类型 */
        $a = array(
            /* 个人 */
            /* 		入款 */
            1010 => '充值',
            1020 => '定单退款',
            /* 		出款 */
            2010 => '支付定单',
            2020 => '提现',
            /* 商家 */
            /* 		入款 */
            3010 => '利润分成',
            3020 => '充值',
            3030 => '提现失败',
            /* 		出款 */
            4010 => '定单退款',
            4020 => '商家结款',
            /* 平台 */
            /* 		入款 */
            5010 => '充值',
            /* 		出款 */
            6010 => '给商家结款'
        );
        $this->moneysetting = $a;


        /* 支付方式 */
        $a = array(
            'wx' => array('id' => 10, 'title' => '微信'),
            'alipay' => array('id' => 20, 'title' => '支付宝'),
            'mymoney' => array('id' => 30, 'title' => '余额支付'),
            'vmoney' => array('id' => 40, 'title' => '赠款支付')
        );
        $this->myway = $a;

        /* 入款方式 */
        $a = array(
            'wx' => array('id' => 10, 'title' => '微信'),
            'alipay' => array('id' => 20, 'title' => '支付宝')
        );
        $this->moneyway = $a;

        /* 提现状态 */
        $a = array(
            'new' => '待审核',
            'checked' => '审核通过',
            'unchecked' => '未通过',
            'done' => '已打款',
            'cancel' => '已取消'
        );
        $this->takestatus = $a;



        /* 虚拟货币入款类型 */
        $this->vmoneyintype = array(
            'form' => '调查',
            'refunds' => '定单退款'
        );

        /* 虚拟货币出款类型 */
        $this->vmoneyouttype = array(
            'pay' => '支付定单'
        );
    }

    /* 充值 1010
     * $action = add, substract
     * 
     * 		--$money['myway'] : 入款方式 wx, alipay
     * $money['title'] 款项方式名称
     * $money['mywayic'] = 支付方式ic
     * $money['amoun'] = 金额
     * $money['formcode'] = 交易单号
     * $money['formdate'] = 凭证日期
     * $money['uid'] = 用户id
     * $money['orderid'] = 定单id
     * $money['comid'] = 店铺id
     * $money['other'] = 备注
     * 
     * $money['duid']
     * $money['dnick']
     * 
     * 下面这几个跟据入同账本有变化
     * $money['action'] = add, substract
     * $money['mytype'] = 款项编码
     * $money['acceptgroup'] = 收款的用户类型 user com plat

     * 入款成功返回true,错误返回false
     */

    function domoney($money) {
        if(!array_key_exists('myfrom', $money)){
            $money['myfrom']='shendeng';
        }
        
        /* if没定duid和dnick then 用当前用户信息 */
        if (!array_key_exists('duid', $money)) {
            $money['duid'] = $this->main->user['id'];
        }
        if (!array_key_exists('dnick', $money)) {
            $money['dnick'] = $this->main->user['u_nick'];
        }


        /* 跟据收款用户类型，决定入哪表 */
        switch ($money['acceptgroup']) {
            case 'user':
                $sheetname = sh . '_moneyuser';
                break;
            case 'com':
                $sheetname = sh . '_moneycom';
                break;
            case 'plat':
                $sheetname = sh . '_moneyplat';
                break;
        }

        /* 检测有没有这笔入款，避免重重入款 */
        if ('' !== $money['formcode']) {
            $sql = 'select count(*) from `' . $sheetname . '` where formcode=:formcode and mytype=:mytype';
            unset($para);
            $para[':formcode'] = $money['formcode'];
            $para[':mytype'] = $money['mytype'];
            $counts = $this->pdo->counts($sql, $para);
            if ($counts > 0) {
                $GLOBALS['errmsg'][] = '已经有这笔交易记录了，交易号：' . $money['formcode'];
                return false;
            }
        }

        /* 如果是出款，检测出款额不能大于可用余额 */
        if ('add' !== $money['action']) {

            if ($money['uid'] * 1 > 0) {              
                if ($this->geticanuse($money['acceptgroup'], $money['uid'], $money['comid']) < $money['amoun']) {
                    $GLOBALS['errmsg'][] = '出款额超过可用余额' . (($money['amoun']-$this->geticanuse($money['acceptgroup'], $money['uid'], $money['comid']))/100).'元';
                    return false;
                }
            }
        }


        $rs['uid'] = $money['uid']; //所属人id
        $rs['unick'] = '';

        /* 入 */
        if ('add' == $money['action']) {
            $rs['myvalue'] = $money['amoun'];
            $rs['myvalueout'] = 0;
        } else { //出
            $rs['myvalue'] = 0;
            $rs['myvalueout'] = $money['amoun'];
        }

        $rs['orderid'] = $money['orderid'];

        $rs['title'] = $money['title'];

        $rs['duid'] = $money['duid'];
        $rs['dnick'] = $money['dnick'];

        $rs['mytype'] = $money['mytype'];
        $rs['mytypename'] = $this->moneysetting[$money['mytype']];

        if ('' !== $money['mywayic']) {
            $rs['myway'] = $this->myway[$money['mywayic']]['id'];
            $rs['mywayname'] = $this->myway[$money['mywayic']]['title'];
        }

        $rs['formcode'] = $money['formcode'];

        if (array_key_exists('formdate', $money)) {
            $rs['formdate'] = $money['formdate'];
        }

        $rs['stimeint'] = time();
        $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);

        $rs['moneytype'] = 1;
        $rs['comid'] = $money['comid'];

        $rs['myip'] = $this->main->getip();

        $rs['mytotal'] = 0; //添加后再更新
         $rs['myfrom'] = $money['myfrom'];
        /* 有备注时把备注也存进去 */
        if (array_key_exists('other', $money)) {
            $rs['other'] = $money['other'];
        }

        $moneyid = $this->pdo->insert($sheetname, $rs);

        if ('add' == $money['action']) {
            $operator = '+';
        } else {
            $operator = '-';
        }


        /* 跟据收款人类型，更新财务统计 */
        switch ($money['acceptgroup']) {
            case 'user':
                /* 更新用户余额 */
                if ($money['uid'] > 0) {/* 非注册用户购买 没有用户 */
                    /* update money user 更新用户财务表的余额 */
                    $this->updatemoneytotalofuser($moneyid, $money['amoun'], $money['uid'], $money['action']);

                    $sql = 'update `' . sh . '_user` set ';
                    $sql .= ' aall=aall' . $operator . $money['amoun'];
                    $sql .= ', acanuse=acanuse' . $operator . $money['amoun'];

                    if ('add' == $money['action']) {
                        $sql .= ', ain=ain+' . $money['amoun'] . ' where 1 ';
                    } else {
                        $sql .= ', aout=aout+' . $money['amoun'] . ' where 1 ';
                    }

                    $sql .= ' and id=:uid';

                    $this->pdo->doSql($sql, Array(':uid' => $money['uid']));
                }
                break;
            case 'com':
                /* 更新商家财务报表余额 */
                $this->updatemoneytotalofcom($moneyid, $money['amoun'], $money['comid'], $money['action']);

                /* 更新店铺用户余额 */
                $sql = 'update `' . sh . '_account` set aall=aall' . $operator . $money['amoun'];
                $sql .= ', acanuse=acanuse' . $operator . $money['amoun'];

                if ('add' == $money['action']) {
                    $sql .= ', ain=ain+' . $money['amoun'];
                } else {
                    $sql .= ', aout=aout+' . $money['amoun'];
                }

                $sql .= ' where 1 ';
                $sql .= ' and comid=:comid';
                $sql .= ' and mytype="biz"';
                $this->pdo->doSql($sql, Array(':comid' => $money['comid']));

                break;
            case 'plat':
                /* 更新平台财务列表余额, */
                $this->updatemoneytotalofplat($moneyid, $money['amoun'], $money['uid'], $money['action']);


                /* 更新平台财务统计 */
                $sql = 'update `' . sh . '_account` set aall=aall' . $operator . $money['amoun'];
                $sql .= ', acanuse=acanuse' . $operator . $money['amoun'];
                if ('add' == $money['action']) {
                    $sql .= ', ain=ain+' . $money['amoun'] . ' where 1 ';
                } else {
                    $sql .= ', aout=aout+' . $money['amoun'] . ' where 1 ';
                }


                $sql .= ' and mytype="plat" ';

                $this->pdo->doSql($sql);
                break;
        }

        return true;
    }

    /* 跟据商家comid提取 uid */

    function getcomuid($comid) {
        $pdo = & $GLOBALS['pdo'];

        $sql = 'select uid from `' . sh . '_com` where 1 ';
        $sql .= ' and id=:comid';

        $result = $pdo->fetchOne($sql, Array(':comid' => $comid));

        return $result['uid'];
    }

    /* 更新用户财务表的余额，包括个人，商家，平台，都用这个更新
     * moneyid : 刚入的这笔款项id
     * allprice ： 入款金额
     * sheetname : 表名，带前缀
     * uid : 用户id，更新平台财务时用户id是0，这时上一条记录就是平台的，不需要uid
     */

    function updatemoneytotalofuser($moneyid, $allprice, $uid, $action) {
        /* 提取上一笔财务的mytotal, if 没找到， 此从个人信息里提取 */
        $sql = 'select mytotal from `' . sh . '_moneyuser` where 1 ';
        $sql .= ' and id<:moneyid ';
        $sql .= ' and uid=:uid';
        $sql .= ' order by id desc limit 1';

        $para[':moneyid'] = $moneyid;
        $para[':uid'] = $uid;

        $result = $this->pdo->fetchOne($sql, $para);

        if (false !== $result) {
            if ('add' == $action) {
                $currentmytotal = $result['mytotal'] + $allprice;
            } else {
                $currentmytotal = $result['mytotal'] - $allprice;
            }
        } else {
            /* 没有上一条记录，从个人款项总额里提 */
            if ('add' == $action) {
                $currentmytotal = $allprice;
            } else {
                $currentmytotal = -$allprice;
            }
        }

        /* 更新这笔款项的mytotal */
        $sql = 'update `' . sh . '_moneyuser` set mytotal=:currentmytotal where id=:moneyid';
        unset($para);
        $para[':currentmytotal'] = $currentmytotal;
        $para[':moneyid'] = $moneyid;
        $this->pdo->doSql($sql, $para);
    }

    /* 更新平台财务表的余额，包括个人，商家，平台，都用这个更新
     * moneyid : 刚入的这笔款项id
     * allprice ： 入款金额
     * sheetname : 表名，带前缀
     * uid : 用户id，更新平台财务时用户id是0，这时上一条记录就是平台的，不需要uid
     */

    function updatemoneytotalofplat($moneyid, $allprice, $uid, $action) {
        /* 提取上一笔财务的mytotal, if 没找到， 此从个人信息里提取 */
        $sql = 'select mytotal from `' . sh . '_moneyplat` where 1 ';
        $sql .= ' and id=:lastmoneyid ';
        $sql .= ' limit 1';

        $para[':lastmoneyid'] = $moneyid - 1;

        $result = $this->pdo->fetchOne($sql, $para);

        if (false !== $result) {
            if ('add' == $action) {
                $currentmytotal = $result['mytotal'] + $allprice;
            } else {
                $currentmytotal = $result['mytotal'] - $allprice;
            }
        } else {
            /* 没有上一条记录，从个人款项总额里提 */
            if ('add' == $action) {
                $currentmytotal = $allprice;
            } else {
                $currentmytotal = -$allprice;
            }
        }

        /* 更新这笔款项的mytotal */
        $sql = 'update `' . sh . '_moneyplat` set mytotal=:currentmytotal where id=:moneyid';
        unset($para);
        $para[':currentmytotal'] = $currentmytotal;
        $para[':moneyid'] = $moneyid;
        $this->pdo->doSql($sql, $para);
    }

    /* 更新店铺财务表的余额
     * moneyid : 刚入的这笔款项id
     * allprice ： 入款金额
     * comid ： 店铺id
     */

    function updatemoneytotalofcom($moneyid, $allprice, $comid, $action) {
        /* 提取上一笔财务的mytotal, if 没找到， 此从个人信息里提取 */
        $sql = 'select mytotal from `' . sh . '_moneycom` where 1 ';
        $sql .= ' and id<:moneyid ';
        $sql .= ' and comid=:comid ';
        $sql .= ' order by id desc limit 1';

        $para[':moneyid'] = $moneyid;
        $para[':comid'] = $comid;

        $result = $this->pdo->fetchOne($sql, $para);

        if (false !== $result) {
            if ('add' == $action) {
                $currentmytotal = $result['mytotal'] + $allprice;
            } else {
                $currentmytotal = $result['mytotal'] - $allprice;
            }
        } else {
            /* 没有上一条记录，从个人款项总额里提 */
            if ('add' == $action) {
                $currentmytotal = $allprice;
            } else {
                $currentmytotal = -$allprice;
            }
        }

        /* 更新这笔款项的mytotal */
        $sql = 'update `' . sh . '_moneycom` set mytotal=:currentmytotal where id=:moneyid';
        unset($para);
        $para[':currentmytotal'] = $currentmytotal;
        $para[':moneyid'] = $moneyid;
        $this->pdo->doSql($sql, $para);
    }

    /* 检测凭证号是否有重复
     * mytype : 款项类型 1010 1020这种
     *  */

    function hasformcode($sheetname, $formcode, $mytype) {
        $sql = 'select count(*) from `' . $sheetname . '` where formcode=:formcode and mytype=:mytype';

        $para[':formcode'] = $formcode;
        $para[':mytype'] = $mytype;

        $counts = $this->pdo->counts($sql, $para);
        if ($counts > 0) {
            return true;
        } else {
            return false;
        }
    }

    /* 增加提现记录
     * $take['uid'] = 提现用户id
     * $take['comid'] = 
     * $take['u_gic'] = 
     * $take['myvalue'] = 提现金额 单位分
     * $take['fullname'] = 
     * $take['payaccount'] = 
     * $take['other'] = 
     * 返回插入的记录id
     */

    function addtakemoney($take) {
        $stimeint = time();

        $rs['uid'] = $take['uid'];
        $rs['comid'] = $take['comid'];
        $rs['u_gic'] = $take['u_gic'];
        $rs['myvalue'] = $take['myvalue'];

        $rs['fullname'] = $take['fullname'];
        $rs['payname'] = $take['payname'];
        $rs['paybank'] = $take['paybank'];
        $rs['payaccount'] = $take['payaccount'];
        $rs['other'] = $take['other'];
        $rs['moneycomidlist'] = $take['moneycomidlist'];


        $rs['duid'] = $this->main->user['id'];
        $rs['mystatus'] = 'new';
        $rs['stime'] = date('Y-m-d H:i:s', $stimeint);
        $rs['stimeint'] = $stimeint;

        $insertid = $this->pdo->insert(sh . '_takemoney', $rs);

        return $insertid;
    }

    /* get提现记录 */

    function gettakemoneybyid($id) {
        $sql = 'select * from `' . sh . '_takemoney` where 1';
        $sql .= ' and id=:id';
        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        return $result;
    }

    /* 可用余额 */

    function geticanuse($group, $uid, $comid) {
        switch ($group) {
            case 'user':
                $sql = 'select acanuse from `' . sh . '_user` where 1';
                $sql .= ' and id=' . $uid;
                break;
            case 'com':
                $sql = 'select acanuse from `' . sh . '_account` where 1';
                $sql .= ' and mytype="biz" ';
                $sql .= ' and comid=' . $comid;
                break;
            case 'plat':
                $sql = 'select acanuse from `' . sh . '_account` where 1';
                $sql .= ' and mytype="plat" ';
                break;
        }

        $result = $this->pdo->counts($sql);

        return $result;
    }

    /* 给用户增加虚拟入款
     * $money['uid'] = 用户id 
     * $money['amoun'] = 金额
     * $money['formcode'] = 交易单号 ,对答卷 是 form_dolistid
     * $money['formdate'] = 凭证日期 form_dolistid时间

     * $money['mytype'] = 
     *      //$money['myway'] = 
     * $money['other'] = 备注
     */

    function vmoneytouser($money) {
        $currenttime = time();

        /* 增加虚拟入款记录 */
        $rs['uid'] = $money['uid'];
        $rs['myvalue'] = $money['amoun'];
        $rs['formcode'] = $money['formcode'];
        $rs['formdate'] = $money['formdate'];

        if (isset($money['other'])) {
            $rs['other'] = $money['other'];
        }


        $rs['mytype'] = $money['mytype'];
        $rs['mytypename'] = $this->vmoneyintype[$rs['mytype']];

        //$rs['myway'] = $money['myway'];
        //$rs['mywayname'] = $this->vmoneyway[$rs['myway']];

        $rs['stime'] = date('Y-m-d H:i:s', $currenttime);
        $rs['stimeint'] = $currenttime;

        $rs['myip'] = $this->main->getip();

        $insertid = $this->pdo->insert(sh . '_vmoneyuser', $rs);

        /* 更新当前余额 */
        $sql = 'select mytotal from `' . sh . '_vmoneyuser` where 1 ';
        $sql .= ' and id<:insertid ';
        $sql .= ' and uid=:uid';
        $sql .= ' order by id desc limit 1';

        $para[':insertid'] = $insertid;
        $para[':uid'] = $this->main->user['id'];

        $result = $this->pdo->fetchOne($sql, $para);

        $lastmoney = $result['mytotal'];

        $sql = 'update `' . sh . '_vmoneyuser` set mytotal=' . ($lastmoney + $money['amoun']);
        $sql .= ' where 1 ';
        $sql .= ' and id=' . $insertid;

        $this->pdo->doSql($sql);


        /* 更新用户个人虚拟款 */
        $sql = 'update `' . sh . '_user` set vmoney = vmoney+' . $money['amoun'];
        $sql .= ' where 1 ';
        $sql .= ' and id=' . $money['uid'];
        $this->pdo->doSql($sql);

        return true;
    }

    /* 储值卡支付
     * $money['uid'] = 用户id 
     * $money['amoun'] = 金额
     * $money['formcode'] = 交易单号 ,对答卷 是 form_dolistid
     * $money['formdate'] = 凭证日期 form_dolistid时间

     * $money['mytype'] = 
     *      //$money['myway'] = 
     * $money['other'] = 备注
     */

    function vmoneypay($money) {
        $currenttime = time();

        /* 扣除虚拟款 */
        $rs['uid'] = $money['uid'];
        $rs['myvalueout'] = $money['amoun'];
        $rs['formcode'] = $money['formcode'];
        $rs['formdate'] = $money['formdate'];

        if (isset($money['other'])) {
            $rs['other'] = $money['other'];
        }


        $rs['mytype'] = $money['mytype'];
        $rs['mytypename'] = $this->vmoneyouttype[$rs['mytype']];
        //$rs['myway'] = $money['myway'];
        //$rs['mywayname'] = $this->vmoneyoutway[$rs['myway']];
        $rs['stime'] = date('Y-m-d H:i:s', $currenttime);
        $rs['stimeint'] = $currenttime;

        $rs['myip'] = $this->main->getip();

        $insertid = $this->pdo->insert(sh . '_vmoneyuser', $rs);

        /* 更新当前余额 */
        $sql = 'select mytotal from `' . sh . '_vmoneyuser` where 1 ';
        $sql .= ' and id<:insertid ';
        $sql .= ' and uid=:uid';
        $sql .= ' order by id desc limit 1';

        $para[':insertid'] = $insertid;
        $para[':uid'] = $this->main->user['id'];

        $result = $this->pdo->fetchOne($sql, $para);

        $lastmoney = $result['mytotal'];

        $sql = 'update `' . sh . '_vmoneyuser` set mytotal=' . ($lastmoney - $money['amoun']);
        $sql .= ' where 1 ';
        $sql .= ' and id=' . $insertid;

        $this->pdo->doSql($sql);


        /* 更新用户个人虚拟款 */
        $sql = 'update `' . sh . '_user` set vmoney = vmoney-' . $money['amoun'];
        $sql .= ' where 1 ';
        $sql .= ' and id=' . $this->main->user['id'];
        $this->pdo->doSql($sql);

        return true;
    }

}

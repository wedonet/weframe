<?php

//require 'mail.php';
/*
 * 孙浥林
 * 
 * getgnamebyic : 跟据用户组ic得到用户组名称
 * getnickbyid : 跟据用户id,得到用户昵称
 * getuserbyid : 跟据用户id,取得用户信息
 */

class cls_user {

    function getgnamebyic($ic) {
        $sql = 'select title from `' . sh . '_group` where 1 ';
        $sql .= 'and ic="' . $ic . '" ';

        $result = $GLOBALS['we']->exeone($sql);

        if (false == $result) {
            return false;
        } else {
            return $result['title'];
        }
    }

    function chkusermail($uid, $mail) {
        
    }

    function getnickbyid($uid) {
        $sql = 'select u_nick from `' . sh . '_user` where 1 ';
        $sql .= ' and id=' . $uid;

        $result = $GLOBALS['we']->exeone($sql);

        if (FALSE == $result) {
            return '';
        } else {
            return $result['u_nick'];
        }
    }

    function getuserbyid($uid) {
        $sql = 'select * from `' . sh . '__user` where 1 ';
        $sql .= ' and id=' . $uid;

        $result = $GLOBALS['we']->exeone($sql);

        return $result;
    }

    /* 保存新用户
     * u_gic -->要添加的用户所在组标识
     * u_roleic
     * 必填项： $rs =>u_name, u_phone, u_mobile, u_mail, u_pass
     */

    function savenewuser($rs, $u_gic = null, $u_roleic = null, $comid = 0) {

        $we = & $GLOBALS['main'];
        $pdo = & $GLOBALS['pdo'];

        /* 跟据ic,取用户组和角色名称 */
        $sql = 'select title from `' . sh . '_group` where 1 ';
        $sql .= ' and ic=:u_gic ';
        $result = $pdo->fetchOne($sql, Array(':u_gic' => $u_gic));

        $rs['u_gic'] = $u_gic;
        $rs['u_gname'] = $result['title'];

        $rs['comid'] = $comid;

        if ('sys' != $u_roleic) {
            $sql = 'select title from `' . sh . '_group` where 1 ';
            $sql .= ' and ic=:u_roleic ';

            $result = $pdo->fetchOne($sql, Array(':u_roleic' => $u_roleic));

            $rs['u_roleic'] = $u_roleic;
            $rs['u_rolename'] = $result['title'];
        } else {
            $rs['u_roleic'] = 'sys';
            $rs['u_rolename'] = '系统';
        }




        /* 检测用户名是否存在 */
        if (array_key_exists('u_name', $rs) AND $this->hasname($rs['u_name'])) {
            $GLOBALS['errmsg'][] = '您填写的用户名已经存在, 请重新填写';
            $GLOBALS['errinput'][] = 'u_name';
            return false;
        }

        /* 检测手机是否存在 */
        if ($this->hasmobile($rs['u_mobile'], '', $u_gic)) {
            $GLOBALS['errmsg'][] = '您填写的手机号已经存在, 请重新填写';
            $GLOBALS['errinput'][] = 'u_mobile';
            return false;
        }

        /* 检测邮箱是否存在,目前不检测了 */

        //其它值
        $rs['u_face'] = '/_images/noface.png';

        $rs['u_err'] = 0;
        $rs['u_searchpasserr'] = 0;
        $rs['u_searchpasserrtime'] = time();


        //最后一次修改人信息
        $rs['etimeint'] = time();
        $rs['etime'] = date('Y-m-d H:i:s', $rs['etimeint']);
        $rs['euid'] = $we->user['id'];
        $rs['enick'] = $we->user['u_nick'];

        //密码处理
        $rs['randcode'] = $we->generate_randchar(8);
        $rs['u_pass'] = md5($rs['u_pass'] . $rs['randcode']);




        //添加信息
        $rs['stimeint'] = time();
        $rs['stime'] = date('Y-m-d H:i:s', $rs['stimeint']);
        $rs['suid'] = $we->user['id'];
        $rs['snick'] = $we->user['u_nick'];

        //if 没专过来ischeck then 认为已经通过审核 不需要审核
        if (!key_exists('ischeck', $rs)) {
            $rs['ischeck'] = 1;
        }

        //$rs['islock'] = 0;
        $rs['u_regtimeint'] = time();
        $rs['u_regtime'] = date('Y-m-d H:i:s', $rs['u_regtimeint']);



        try {
            $we->pdo->begintrans();

            $id = $we->pdo->insert(sh . '_user', $rs);


            /* 更新这个用户组的会员数 */
            $sql = 'update `' . sh . '_group` set countuser= (select count(*) from ' . sh . '_user where u_gic="' . $u_gic . '") where ic="' . $u_gic . '"';
            $pdo->doSql($sql);

            /* if roleic不等于系统 then更新这个角色会员数 */
            if ('sys' != $u_roleic) {
                $sql = 'update `' . sh . '_group` set countuser= (select count(*) from ' . sh . '_user where u_roleic="' . $u_roleic . '") where ic="' . $u_roleic . '"';
                $pdo->doSql($sql);
            }

            $pdo->submittrans();
        } catch (PDOException $e) {
            $GLOBALS['errmsg'][] = $e->getMessage();

            $we->pdo->rollbacktrans();
            return false;
        }




        return true;
    }

    /* 是否有重复用户名 */

    function hasname($v, $myid = '', $u_gic = null) {
        $sql = 'select count(*) from `' . sh . '_user` where u_name=:u_name';

        if (null !== $u_gic) {
            $sql .= ' and u_gic="' . $u_gic . '" ';
        }

        /* $sql .= ' and comisdel=0 '; */
        $sql .= ' and isdel=0 ';

        if ('' !== $myid) {
            $sql .= ' and id<>' . $myid;
        }
//stop($sql);
        $para['u_name'] = $v;

        if ($GLOBALS['pdo']->counts($sql, $para) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* 是否有重复手机号 
      当u_gic不等于空时，只检测这个组的会员有没有重复
     *      */

    function hasmobile($v, $myid = '', $u_gic = null) {
        $sql = 'select count(*) from `' . sh . '_user` where u_mobile=:u_mobile';

        if (null !== $u_gic) {
            $sql .= ' and u_gic="' . $u_gic . '" ';
        }
        /* $sql .= ' and comisdel=0 '; */
        //$sql .= ' and isdel=0 ';

        if ('' !== $myid) {
            $sql .= ' and id<>' . $myid;
        }

        $para['u_mobile'] = $v;

        if ($GLOBALS['pdo']->counts($sql, $para) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* 是否有重复邮箱 */

    function hasmail($v, $myid = '') {
        $sql = 'select count(*) from `' . sh . '_user` where u_mail=:u_mail';
        /* $sql .= ' and comisdel=0 '; */
        //$sql .= ' and isdel=0 ';

        if ($myid !== '') {
            $sql .= ' and id<>' . $myid;
        }

        $para['u_mail'] = $v;

        if ($GLOBALS['pdo']->counts($sql, $para) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* 是否有重复昵称 */

    function hasnick($v, $myid = '') {
        $sql = 'select count(*) from `' . sh . '_user` where u_nick=:u_nick';
        /* $sql .= ' and comisdel=0 '; */
        //$sql .= ' and isdel=0 ';

        if ($myid !== '') {
            $sql .= ' and id<>' . $myid;
        }

        $para['u_nick'] = $v;

        if ($GLOBALS['pdo']->count($sql, $para) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* 是否有重复身份证 */

    function hasidcode($v, $myid = '') {
        if ('' == $v) {
            return false;
        }

        $sql = 'select count(*) from `' . sh . '_user` where 1 ';
        //$sql .= ' and comid=' . $GLOBALS['we']->user['u_hotelid'];
        $sql .= ' and u_idcode=:u_idcode';
        /* $sql .= ' and comisdel=0 '; */
        $sql .= ' and isdel=0 ';

        if ($myid !== '') {
            $sql .= ' and id<>' . $myid;
        }

        $para['u_idcode'] = $v;

        if ($GLOBALS['pdo']->counts($sql, $para) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* 检测用户登录 
     * 
     */

    function checklogin($u_name, $u_pass, $u_gic, $u_roleic = '', $u_mobile = '') {
        if ('' != $u_name) {
            $myicname = '用户名';

            $sql = 'select * from `' . sh . '_user` where 1 ';
            $sql .= ' and u_name=:u_name ';
            $sql .= ' and isdel=0 ';
            $sql .= ' limit 1 ';

            $result = $GLOBALS['pdo']->fetchOne($sql, Array(':u_name' => $u_name));
        }
        if ('' != $u_mobile) {
            $myicname = '手机号';

            $sql = 'select * from `' . sh . '_user` where 1 ';
            $sql .= ' and u_mobile=:u_mobile ';
            $sql .= ' and u_gic="' . $u_gic . '"';
            $sql .= ' and isdel=0 ';
            $sql .= ' limit 1 ';

            $result = $GLOBALS['pdo']->fetchOne($sql, Array(':u_mobile' => $u_mobile));
        }

        //stop($result,true);
        if (empty($result)) {
            $GLOBALS['errmsg'][] = '该用户不存在';
            return false;
        }
        /* 密码不对直接返回 */
        if ($result['u_pass'] !== md5($u_pass . $result['randcode'])) {
            $GLOBALS['errmsg'][] = $myicname . '或密码错误';
            return false;
        }

        /* 检测审核 */
        if (1 != $result['ischeck'] . '') {
            $GLOBALS['errmsg'][] = '您还没通过审核,请稍后登录';
            return FALSE;
        }

        /* 检测锁定 */
        if (1 == $result['islock'] . '') {
            $GLOBALS['errmsg'][] = '您已经被管理员锁定';
            return FALSE;
        }

        /* 检测用户组和角色是否正确 */
        if ($u_gic != $result['u_gic']) {
            $GLOBALS['errmsg'][] = '您没有权限登录此页面';
            return FALSE;
        }

        if ('' != $u_roleic) {
            if ($u_roleic != $result['u_roleic']) {
                $GLOBALS['errmsg'][] = '没有登录此页面的权限';
                return false;
            }
        }

        /* 在这里生成用户名的cookie */
        setcookie(CacheName . 'uname', $result['u_name'], time() + 3600 * 24 * 30, '/');

        $_SESSION[CacheName . 'user'] = $this->getuserpara($result);

        return true;
    }

    /* 重载用户信息 */

    function reloaduser($uid) {
        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and id=:uid ';
        $sql .= ' and isdel=0 ';
        $sql .= ' limit 1 ';

        $result = $GLOBALS['pdo']->fetchOne($sql, Array(':uid' => $uid));

        /* 在这里生成用户名的cookie */
        setcookie(CacheName . 'uname', $result['u_name'], time() + 3600 * 24 * 30, '/');



        $_SESSION[CacheName . 'user'] = $this->getuserpara($result);

        return true;
    }

    function getuserpara(&$result) {
        /* 生成用户信息session */
        $user['id'] = $result['id'];
        $user['u_name'] = $result['u_name'];
        $user['u_nick'] = $result['u_nick'];
        $user['u_fullname'] = $result['u_fullname'];

        $user['u_mobile'] = $result['u_mobile'];

        $user['u_gic'] = $result['u_gic'];

        $user['u_gname'] = $result['u_gname'];

        $user['u_roleic'] = $result['u_roleic']; //角色编码
        $user['u_rolename'] = $result['u_rolename']; //角色编码

        $user['u_face'] = $result['u_face'];

        //$user['u_ismaster'] = 0;

        $user['comid'] = $result['comid'];
        $user['comname'] = $result['comname'];


        $user['isreple'] = 0; //是否有补货权限

        return $user;
    }

    function loginout() {
        unset($_SESSION[CacheName . 'user']);
        session_destroy();
    }

}

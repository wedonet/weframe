<?php

require_once(adminpath . '/admin.php');

/* 神灯管理员后台 */

class cls_modulemain {

    function __construct() {

        $this->j = & $GLOBALS['j'];
    }

    /* 检测权限
     * 返回 true,false
     */

    function haspower() {

        if ('admin' == $this->j['user']['u_gic']) { //有权限
            $this->j['errcode'] = 0;
            $access = str_replace(adminname, '', ltrim($_SERVER['PHP_SELF'], "/"));
        $access = str_replace(".php", '', $access);
        $access = ltrim($access, '/');
        $querys = explode('&', $_SERVER['QUERY_STRING']);
        if (!empty($_GET['act'])) {
            $access = $access . '/' . trim($_GET['act']);
        } elseif (!empty($_POST['act'])) {
            $access = $access . '/' . trim($_POST['act']);
        }
        $u_roleic = $GLOBALS['j']['user']['u_roleic'];
        if ($u_roleic !== 'sys') {//sys是超级管理员 不做权限判断
            if (empty($u_roleic)) {//无法获取u_roleic判断失败 跳转
//                $this->j['errcode'] = 1000;
                $this->j['success'] = 'n';
                $this->j['errmsg'][] = '没有权限，请联系超级管理员';
                                return FALSE;

            }
            $sql = "select access_id from " . sh . "_group where ic='$u_roleic'";
            $accessid = $GLOBALS['pdo']->fetchOne($sql);
            $accessid = trim(str_replace('|', ',', $accessid['access_id']), ',');
            if (empty($accessid)) {//获取不到权限id 判断失败 跳转
//                 $this->j['errcode'] = 1000;
                $this->j['success'] = 'n';
                $this->j['errmsg'][] = '没有权限，请联系超级管理员';
                                return FALSE;

            }
            $sql = "select name from " . sh . "_access where id in ($accessid)";
            $accesses = $GLOBALS['pdo']->fetchAll($sql);
            foreach ($accesses as $v) {
                $allow_access[] = $v['name'];
            }
            $GLOBALS['allow_access'] = $allow_access; //赋值给global 以便在全局使用    
            if (!in_array($access, $allow_access)) {//当前操作名不在权限名列表中 判断失败 跳转
//                 $this->j['errcode'] = 1000;
                $this->j['success'] = 'n';
                $this->j['errmsg'][] = '没有权限，请联系超级管理员';
                return FALSE;
            }
        }
            return true;
        } else { //没权限
            $this->j['errcode'] = 1000;
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '<a href="' . admindir . 'login.php">已掉线，请重新登录！</a>';
            return false;
        }

        
    }

}

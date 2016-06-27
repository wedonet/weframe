<?php
/* 个人中心，公共类 */
class cls_modulemain {

    function __construct() {
        $this->j = & $GLOBALS['j'];       
    }



    /* 检测权限
     * 返回 true,false
     */

    function haspower() { 
        if ('user' == $GLOBALS['j']['user']['u_gic']) {
            $this->j['errcode'] = 0;
            return true;
        } else {
            $this->j['errcode'] = 1000;
            $this->j['success'] = 'n';
            $this->j['errmsg'][]='已掉线，请重新登录！';
            return false;
        }
    }
}
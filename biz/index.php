<?php
/*店铺后台首页*/
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        
        /* 跟据act确定输出格式 */
        $jsonact = array();
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }
        
        require_once(ApiPath . '/biz/_index.php');//访问接口
        
        require_once(  'checkbiz.php' ); //检测权限

        switch ($this->act) {
            case '':
                $this->fname = 'pagemain'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            default:
                break;
        }
    }

    function pagemain() {
        
    }

}

$tp = new myclass();
unset($tp);

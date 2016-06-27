<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_biz.php';


require_once AdminApiPath . '_main.php'; /* 业务管理通用数据 */

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

        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
        }
    }

    function pagemain() {
        
    }
    
    function refreshordergoods(){
        $c_biz = new cls_biz();
        
        /*get all orders*/
        $sql = 'select id from `'.sh.'_order` ';
        
        $result = $this->pdo->fetchAll($sql);
        
        set_time_limit(600);
        
        foreach($result as $v){
            $c_biz->updateordergoods($v['id']);
            
            echo '更新定单'.$v['id'].'成功！<br />';
        }
        return true;
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源
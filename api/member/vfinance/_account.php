<?php

/*个人中心 储值卡接口*/
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'member/_main.php'; //用户后台通用数据
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
//                $this->output();
                break;
        }
    }

	function pagemain() {  
		$sql = 'select * from `'.sh.'_user` where 1 ';
                $sql .= 'and id=:id';
                $para[':id'] = $this->main->user['id'];
		
		$result = $this->pdo->fetchOne($sql, $para);
		
		$this->j['vmoney'] = $result['vmoney'];
	}

}

$myapi = new myapi();
unset($myapi);
<?php
/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
require_once ApiPath . '_adminxxx1/_main.php';
/**
* 
*/
class admin_business_repairhistory extends cls_api {
	
	function __construct() {
		parent::__construct();
				
 $this->modulemain = new cls_modulemain();
	
			/* 检测权限 */
			if (!$this->modulemain->haspower()) {
				$this->output();
				return false;
			}
			

		$this->act = $this->main->ract();

		switch ($this->act) {
			case '':
				$this->mylist();
				$this->output();
				break;
			
			default:
				break;
		}
	}

	function mylist(){
        $sql = 'select * from `' .sh. '_failtofix` where 1=1 and isend=1';
        $sql .= ' order by id desc';
        $rs = $this->main->exers($sql);
        $this->j['list'] = $rs;
	}
}

$admin_business_repairhistory = new admin_business_repairhistory;
unset($admin_business_repairhistory);
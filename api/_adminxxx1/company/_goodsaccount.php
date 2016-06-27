<?php

/* 店铺商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once AdminApiPath . '_main.php';
require_once '_main.php'; /* 店铺业务管理通用数据 */

/* 返回用户组 */

class myapi extends cls_api {

	function __construct() {
		parent::__construct();
                  $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

		$c_company = new cls_companymain();

		$this->comid = $this->main->rid('comid');

		/* 店铺信息添加进$globals['j']['company'] */
		$this->j['company'] = $c_company->getcompany($this->comid);

		$this->act = $this->main->ract();

		switch ($this->act) {
			case '':
				$this->mylist();
				$this->output();
				break;
			case 'export':
				$this->mylist();
				break;
		}
	}

	/* 全部售卖品，包括已选择的平台商品和店铺自营 */

	function mylist() {
		$this->posttype = 'post';
		$date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
		$date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

		/* 传回前端 */
		$this->j['search']['date1'] = $date1;
		$this->j['search']['date2'] = $date2;


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

		if ($date1 > $date2) {
			$this->ckerr('开始时间不能大于结止时间');
			return false;
		}

		$this->j['search']['date1'] = date('Y-m-d', $date1_int);
		$this->j['search']['date2'] = date('Y-m-d', $date2_int);

		/* 提取所有店铺商品 */
		$sql = 'select comgoods.*,goods.title as title,goods.comid as comid from `' . sh . '_comgoods` as comgoods ';
		$sql .= ' left join `' . sh . '_goods` as goods on comgoods.goodsid=goods.id ';
		$sql .= ' where 1 ';
		$sql .= ' and comgoods.comid=' . $this->comid;

		$result = $this->pdo->fetchAll($sql);
        
		$list = false;
		/* 循环商品，把comgoodsid当索引 */
		foreach ($result as $v) {
			$list[$v['id']] = $v;
			$list[$v['id']]['mycount'] = 0;
		}



		$sql = 'select * from `' . sh . '_order` where 1 ';
		$sql .= 'and ispayed=1';
		$sql .= ' and comid=' . $this->comid;

		/* date */
		$sql .= ' and stimeint>=:date1_int';
		$sql .= ' and stimeint<=:date2_int';
		$para[':date1_int'] = $date1_int;
		$para[':date2_int'] = $date2_int + (24 * 3600);

		$result = $this->pdo->fetchAll($sql, $para);

		if (false != $list) {
			foreach ($result as $v) {
				$comgoodsids = $v['comgoodsids'];
				$a = explode(',', $comgoodsids);

				foreach ($a as $z) {
					if(array_key_exists($z, $list)){
						$list[$z]['mycount']++;
					}
				}
			}
		}


		$this->j['list'] = $list;
	}

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源

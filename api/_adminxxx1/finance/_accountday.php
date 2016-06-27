<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . '_adminxxx1/_main.php';


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


        $this->act = $this->main->ract();

        switch ($this->act) {
            case '':
                $this->main();
                $this->output();
                break;
             case 'export':
                $this->main();
                $this->output();
                break;
        }
    }

    /*
     * 结点
     *  account
     *  list
     *     */

    function main() {
        $j = & $GLOBALS['j'];

        /* 初始化 */


        /* 接收参数 */
        $this->posttype = 'post';
        $comic = $this->main->request('comic', '店铺编码', 1, 50, 'char', '', false);
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

        /* 传回前端,兼初始化 */
        $this->j['search']['comic'] = $comic;
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['comname'] = '';

        if (!$this->ckerr()) {
            return false;
        }


        /* check com，并取得店铺信息 */
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

        if ($date1_int > $date2_int) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }

        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);



        $sql = 'select sum(myvalue) as myvalue,';
        $sql .= ' sum(myvalueout) as myvalueout,';
        $sql .= ' min(stimeint) as stimeint, ';
//        $sql .= ' floor(stimeint/86400)*86400 as sdayint,';//这样计算或出现错误
        $sql .= ' FROM_UNIXTIME(stimeint,"%Y-%m-%d") as sdayint,';//采用mysql时间函数计算
        $sql .= ' count(id) as mycount ';
        $sql .= ' from `' . sh . '_moneyplat` where 1 ';

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

        $sql .= ' group by sdayint';
        $sql .= ' order by id desc';
        
        if ('export' == $this->act) {
            $result = $this->pdo->fetchAll($sql, $para);
            $this->j['list'] = $result;
            return;
             }else{

        $result = $this->main->exers($sql, $para);
             $this->j['list'] = $result;
             
             }
        /* 统计 */
        $sql = 'select sum(myvalue) as myvalue,sum(myvalueout) as myvalueout, count(*) as mycount from `' . sh . '_moneyplat` where 1 ';
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

        $result = $this->pdo->fetchOne($sql, $para);
        $this->j['account'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

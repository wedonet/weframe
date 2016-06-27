<?php

/* 平台财务接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_money.php';
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

    function main() {
        $c_money = new cls_money();
        $moneysetting = $c_money->moneysetting;


        $this->posttype = 'post';
        $comic = $this->main->request('comic', '店铺编码', 1, 50, 'char', '', false);
        $comtitle = $this->main->request("comtitle", "店铺名称", 0, 255, 'char', '',false);
        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);

        /* 传回前端 */
        $this->j['search']['comic'] = $comic;
        $this->j['search']['comtitle'] = $comtitle;
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        $this->j['search']['comname'] = '';
        if (!$this->ckerr()) {
            return false;
        }

        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }
        /* check com */
        if ('' != $comic) {
            $sql = 'select id,title from `' . sh . '_com` where ic=:comic';
            $result = $this->pdo->fetchOne($sql, Array(':comic' => $comic));
            if (false == $result) {
                $this->ckerr('没有找到这个店铺');
                 $j['success']='n';
                return false;
            } else {
                $a_com = $result;
                $this->j['search']['comname'] = $result['title'];
            }
        } else {
            $this->j['search']['comname'] = '';
        }
  if ('' != $comtitle) {
            $sql = 'select id,title from `' . sh . '_com` where title like "%' . $comtitle . '%"' ;
            $result = $this->pdo->fetchOne($sql);
//            print_r($result);die;
            if (false == $result) {
                $this->ckerr('没有找到这个店铺');
                 $j['success']='n';
                return false;
            } else {
                $a_com = $result;
                $this->j['search']['comname'] =$comtitle;
            }
        } else {
            $this->j['search']['comname'] = '';
        }
   if ('' != $comic && '' != $comtitle) {
            $sql = 'select id,title from `' . sh . '_com` where ic=:comic and title like "%' . $comtitle . '%"';
            $result = $this->pdo->fetchOne($sql, Array(':comic' => $comic));
            if (false == $result) {
                $this->ckerr('没有找到这个店铺');
                 $j['success']='n';
                return false;
            } else {
                $a_com = $result;
                $this->j['search']['comname'] = $comtitle;
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



        $this->j['search']['date1'] = date('Y-m-d', $date1_int);
        $this->j['search']['date2'] = date('Y-m-d', $date2_int);



        $sql = 'select money.*,com.title as comname ,com.ic as comic from `' . sh . '_moneyplat` as money ';
        $sql .= ' left join `' . sh . '_com` as com on money.comid=com.id ';
        $sql .= ' where 1 ';

        /* 搜店铺 */
        if ('' != $comic) {
            $sql .= ' and money.comid=:comid';
            $para[':comid'] = $a_com['id'];
        }
        if ('' != $comtitle) {
            $sql.= ' and com.title like "%' . $comtitle . '%" ';
        } 

        /* date */
        $sql .= ' and money.stimeint>=:date1_int';
        $sql .= ' and money.stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);


        $sql .= ' order by money.id desc ';

        if ('export' == $this->act) {
            $result = $this->pdo->fetchAll($sql, $para);
            $i = 0;
            foreach ($result as $v) {
                $result[$i]['mytypename'] = $moneysetting[$v['mytype'] * 1];
                $i++;
            }

            $this->j['list'] = $result;
            $j['success']='y';
            return;
        }

        $result = $this->main->exers($sql, $para);

        /* 格式化款项类型 */
        $i = 0;
        foreach ($result['rs'] as $v) {
            $result['rs'][$i]['mytypename'] = array_key_exists($v['mytype'], $moneysetting) ? $moneysetting[$v['mytype'] * 1] : '未知款项:' . $v['mytype'];
            $i++;
        }

        $this->j['list'] = $result;





        /* 统计 */
        $sql = 'select sum(myvalue) as myvalue,sum(myvalueout) as myvalueout, count(*) as mycount from `' . sh . '_moneyplat` as money';
        $sql .= ' left join `' . sh . '_com` as com on money.comid=com.id ';
        $sql .= '  where 1 ';
        /* 搜店铺 */
        if ('' != $comic) {
            $sql .= ' and money.comid=:comid';
            $para[':comid'] = $a_com['id'];
        }
        if ('' != $comtitle) {
            $sql.= ' and com.title like "%' . $comtitle . '%" ';
        } 
        /* date */
        $sql .= ' and money.stimeint>=:date1_int';
        $sql .= ' and money.stimeint<=:date2_int';
        $para[':date1_int'] = $date1_int;
        $para[':date2_int'] = $date2_int + (24 * 3600);

        $result = $this->pdo->fetchOne($sql, $para);
        $this->j['account'] = $result;
    }

}

$myapi = new myapi();
unset($myapi);

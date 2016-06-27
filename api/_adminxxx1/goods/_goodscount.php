<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once AdminApiPath . '_main.php';

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

        switch ($this->act) {
            case '':
                $this->mylist();
                $this->output();
                break;
            case 'count_old_num':
                $this->count_old_num();
                break;
            case 'export':
                $this->mylist();
                $this->output();
                break;
        }
    }

    /* 计算原有订单中已售商品数量 */

    function getsalenum() {

        $countsql = 'select gids from `' . sh . '_order` where mystatus in("payed","taken") ';
        if (!empty($this->comids)) {
            $countsql .= ' and comid in (' . $this->comids . ')';
        }//如果有店铺关键词查询  则加入店铺限制          
        $countsql .= ' and stimeint>=:date1_int';
        $countsql .= ' and stimeint<=:date2_int';
        $countres = $this->pdo->fetchAll($countsql, $this->countpara);
        //加入时间限制

        if (empty($countres)) {
            return [];
        }
        foreach ($countres as $v) {
            if ($v) {
                $vs = explode(',', $v['gids']);
                foreach ($vs as $m) {
                    if (isset($totle[$m])) {
                        $totle[$m] ++;
                    } else {
                        $totle[$m] = 1;
                    }
                }
            }
        }//循环统计每个商品数量这里不区分单品组合品

        $goodsid = array_keys($totle); //所有统计到的商品id
        $goodinfo = 'select id,isgroup,mygroup from `' . sh . '_goods` where id in(' . implode(",", $goodsid) . ')';
        $goodinfos = $this->pdo->fetchAll($goodinfo); //查询下是单品还是组合品  对于组合品需要再次计算数量
        foreach ($goodinfos as $v) {
            if (!empty($v['mygroup'])) {//是组合品
                $groupnum = $totle[$v['id']]; //该组合品的数量
                //echo $v['id'] . "是组合商品，商品信息如下$groupnum<br/><pre>";
                $mygroup = json_decode($v['mygroup']); //获取组合品中的单品信息
//                print_r($mygroup);
//                echo '</pre><br/>';
                foreach ($mygroup as $vs) {//对组合品中的单品再次计算
                    if (isset($totle[$vs->id])) {
                        $totle[$vs->id]+=$vs->count * $groupnum;
                    } else {
                        $totle[$vs->id] = $vs->count * $groupnum;
                    }
                }
            }
        }

        return $totle;
    }

    /* 整体统计原有订单中已售商品数量将计算结果保存到数据库 这个暂时不用啦 */

    function count_old_num() {
        $countsql = 'select gids from `' . sh . '_order` where mystatus in("payed","taken") ';
        $countres = $this->pdo->fetchAll($countsql);
        foreach ($countres as $v) {
            if ($v) {
                $vs = explode(',', $v['gids']);
                foreach ($vs as $m) {
                    if (isset($totle[$m])) {
                        $totle[$m] ++;
                    } else {
                        $totle[$m] = 1;
                    }
                }
            }
        }
        $goodsid = array_keys($totle);
        $goodinfo = 'select id,isgroup,mygroup from `' . sh . '_goods` where id in(' . implode(",", $goodsid) . ')';
        $goodinfos = $this->pdo->fetchAll($goodinfo);
        foreach ($goodinfos as $v) {
            if (!empty($v['mygroup'])) {
                $groupnum = $totle[$v['id']];
                echo $v['id'] . "是组合商品，商品信息如下$groupnum<br/><pre>";
                $mygroup = json_decode($v['mygroup']);
                print_r($mygroup);
                echo '</pre><br/>';
                foreach ($mygroup as $vs) {
                    if (isset($totle[$vs->id])) {
                        $totle[$vs->id]+=$vs->count * $groupnum;
                    } else {
                        $totle[$vs->id] = $vs->count * $groupnum;
                    }
                }
            }
        }
        foreach ($totle as $k => $v) {
            $sql = 'update `' . sh . '_goods` set salenum=' . $v . '  where id=:id';

            $rs = $this->pdo->doSql($sql, Array(':id' => $k));
            if ($rs) {
                print_r($rs);
                echo "id : $k 成功更新 值为 $v <br/>";
            } else {
                print_r($rs);
                echo "id : $k 无需更新<br/>";
            }
        }
    }

    function mylist() {
        /* 接收参数 */
        $this->posttype = 'get';
        $title = $this->main->request('title', '商品名称', 1, 50, 'char', '', false);
        $u_nick = $this->main->request('u_nick', '店铺名称', 1, 50, 'char', '', false);
        $ic = $this->main->request('ic', '商品ic', 1, 50, 'char', '', false);

        $date1 = $this->main->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->main->request('date2', '结止时间', 1, 50, 'date', '', false);
//        print_r( $GLOBALS['errmsg']);
        $this->j['search']['title'] = $title;
        $this->j['search']['u_nick'] = $u_nick;
        $this->j['search']['ic'] = $ic;
        $this->j['search']['date1'] = $date1;
        $this->j['search']['date2'] = $date2;
        if (!$this->ckerr()) {
            return false;
        }
        if ($date1 > $date2) {
            $this->ckerr('开始时间不能大于结止时间');
            return false;
        }
        /* 传回前端 */

        /* 提取所有商品 */
        $para = Array();
        $sql = 'select * from `' . sh . '_goods` where isgroup=0 ';
        if ('' != $title) {

            $sql .= ' and title like :title';
            $para[':title'] = '%' . $title . '%';
        }
        if ('' != $ic) {

            $sql .= ' and ic = :ic';
            $para[':ic'] = $ic ;
        }

        //如果需要关联店铺名称，首先根据关键字查找店铺id 并加到where条件去
        if (!empty($u_nick)) {
            $comsql = 'select id from `' . sh . '_com` where  title = :u_nick';
            $compara[':u_nick'] = $u_nick;
//            print_r($compara);
            $cominfo = $this->pdo->fetchAll($comsql, $compara);
//            print_r($cominfo);
            foreach ($cominfo as $v) {
                $comid[] = $v['id'];
            }//根据关键词查询到相关店铺id
            // print_r($comid);
            if (!empty($comid)) {
                $comids = $this->comids = implode(',', $comid);
                $comgoodssql = 'select goodsid from `' . sh . '_comgoods` where  comid in(' . $comids . ')';
                $comgoodsinfo = $this->pdo->fetchAll($comgoodssql);
                foreach ($comgoodsinfo as $v) {
                    $comgoodsid[] = $v['goodsid'];
                }

                if (!empty($comgoodsid)) {
                    $comgoodsid = array_unique($comgoodsid); //多个店铺可能拥有同一个商品 所有去重
                    $sql .= ' and id in (' . implode(',', $comgoodsid) . ')';
                } else {
                    return;
                }
            } else {//根据店铺id查询到这些店铺拥有的商品id
                return;
            }
        }
        $sql .= ' order by id desc ';

        if ($this->act == 'export') {

            $a_goods = $this->pdo->fetchAll($sql, $para, true);
        } else {
            $a_goods = $this->main->exers($sql, $para);
        }


        //统计当前页商品的浏览量  可直接使用$countarray[goodsid] 调用
        foreach ($a_goods['rs'] as $vs) {
            $ids[] = $vs['id'];
        }//本页的所有商品id
        if (!empty($ids)) {
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
            $countsql = 'select goodsid, count(id) as count from `' . sh . '_counts` where goodsid in (' . implode(',', $ids) . ') ';
            $countsql .= ' and stimeint>=:date1_int';
            $countsql .= ' and stimeint<=:date2_int';
            if (!empty($comid)) {
                $countsql .= ' and comid in (' . implode(',', $comid) . ')';
            }
            $this->countpara[':date1_int'] = $date1_int; //加到类变量中 方便查询已售量使用
            $this->countpara[':date2_int'] = $date2_int + (24 * 3600);
            $countsql.=" group by goodsid ";
            $countres = $this->pdo->fetchAll($countsql, $this->countpara);
            foreach ($countres as $vv) {
                $countarray[$vv['goodsid']] = $vv['count'];
            }
            $salenum_array = $this->getsalenum();
        }


        //开始计算已经售出商品数量




        $this->j['list'] = $a_goods;
        if (!empty($countarray)) {
            $this->j['countarray'] = $countarray;
        }
        if (!empty($salenum_array)) {
            $this->j['salenum_array'] = $salenum_array;
        }
    }

    /* 取店铺信息 */

    function getdata() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_goods` where id=:id ';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $this->j['data'] = $result;
    }

    function getbigimg($s) {
        /* 是缩略图 */
        if (strpos($s, '/thumb/') > 0) {
            return str_replace('thumb/', '', $s);
        } else {
            return $s;
        }
    }

    /* 检测绷带合品，并生成json字串
     * 原格式 ： 1*2，200*1， 格式化生成json字串
     *  */

    function getmygroup($res) {
        $mygroup = Array();

        /* 如果是组合品，必须填组合品id */

        if ('0' == $res['isgroup']) {
            return '';
        }

        if ('' == $res['mygroup']) {
            $GLOBALS['errmsg'][] = '请填写组合品';
            return false;
        }
        $a = explode(',', $res['mygroup']);

        /* 循环每组商品，检测输入格式 */
        $i = 0;
        foreach ($a as $v) {
            if (false === strpos($v, '*')) {
                $GLOBALS['errmsg'][] = '组合品格式错误!';
                return false;
            }

            /* 这组商品 */
            $thisgoods = explode('*', $v);

            /* 有星号检测两边是否数字 */
            if (!$this->main->isint($thisgoods[0]) OR ! $this->main->isint($thisgoods[1])) {
                $GLOBALS['errmsg'][] = '组合品格式错误!';
                return false;
            }


            $mygroup[$i]['id'] = $thisgoods[0];
            $mygroup[$i]['count'] = $thisgoods[1];

            $i++;
        }


        /* 检测商品是否存在 */
        foreach ($mygroup as $v) {
            $sql = 'select count(*) from `' . sh . '_goods` where 1 ';
            $sql .= ' and id=' . $v['id'];

            $counts = $this->pdo->counts($sql);

            if (0 == $counts) {
                $GLOBALS['errmsg'][] = '组合品不存在,id:' . $v['id'];
                return false;
            }
        }

        return json_encode($mygroup);
    }

}

$myapi = new myapi();
unset($sys_admin_user);

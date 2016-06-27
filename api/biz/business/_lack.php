<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'biz/_main.php';

/**
 * 
 */
class admin_business_lack extends cls_api {

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

    function mylist() {
        $comid = $this->main->user['comid'];
        $sql = 'select door.*,goods.title as goodstitle,place.building,place.floor,place.title as placetitle';
        $sql .= ' from `' . sh . '_door` as door ';
        $sql.='  left join `' . sh . '_place` as place  on door.placeid=place.id';
        $sql .= '  left join `' . sh . '_goods` as goods  on door.goodsid=goods.id';
        $sql .= ' where door.hasgoods=0 and door.comid=' . $comid;
        $sql .= ' group by door.deviceic,door.goodsid';
        //$sql .= ' order by door.deviceic';
        //print_r($sql);die;
//        $rs =$this->pdo->fetchAll($sql);
         $rs = $this->main->exers($sql);
          $this->j['total']=$rs['total'];
         $rs=$rs['rs'];
        $deviceic='';//不同的设备商品会一样，标识下一个设备，因为按door.deviceic分组了
          $this->j['list'] =array();
          // $j['list']=array();
        foreach ($rs as $v) {
            if ($v['deviceic'] != $deviceic) {
                $quer = array();              
            }
            $deviceic = $v['deviceic'];
            if (!in_array($v['goodsid'], $quer)) {//如果同一个设备，装了两个格子一样的商品，则只求一次数量，因为按door.goodsid分组了
                   $quer[].=$v['goodsid'];           
                    $sqla = 'select count(goodsid) as count';
                    $sqla .= ' from `' . sh . '_door`  ';
                    $sqla .= ' where hasgoods=0 and comid=' . $comid;
                    $sqla .= ' and  goodsid="' . $v['goodsid'] . '"';
                    $sqla .= ' and deviceic="' . $v['deviceic'] . '"';
                    //print_r($sqla);die;
                    $rsa = $this->pdo->fetchOne($sqla);
                      $p['id'] = $v['id'];
                    
                    $p['pw'] =  $v['building'] . '栋-' . $v['floor'] . '层-' . $v['placetitle'];
                    $p['goodstitle'] = $v['goodstitle'];
                    $p['num'] = $rsa['count'];
                    $p['goodsid'] = $v['goodsid'];
                  
            }
            array_push($this->j['list'], $p);
    
        }
          
        // $this->j['list']['total']=  count($this->j['list']);
     
    }

}

$admin_business_lack = new admin_business_lack;
unset($admin_business_lack);

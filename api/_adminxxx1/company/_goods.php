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



        switch ($this->act) {
            case '':
                $this->mylist();
                $this->output();
                break;
            case 'select':
                $this->selectgoods();
                $this->output();
                break;
            case 'sell': //出售某商品
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dosell();
                $this->output();
                break;
            case 'saveprice': //保存商品价格
                $_POST['outtype'] = 'json'; //输出json格式
                $this->saveprice();
                $this->output();
                break;
            case 'del': //删除平台会员
                $_POST['outtype'] = 'json'; //输出json格式
                $this->dodel();
                $this->output();
                break;
            case 'alarm':
                $this->formalarm();
                $this->output();
                break;
            case 'savealarm':
                $this->savealarm();
                $this->output();
                break;
            case 'toplat':
                $this->toplat();
                $this->output();
                break;
            case 'dotoplat':
                $this->dotoplat();
                $this->output();
                break;
        }
    }

    /* 全部售卖品，包括已选择的平台商品和店铺自营 */

    function mylist() {
        $sql = 'select comgoods.* ';
        $sql .= ' ,goods.title,goods.comid as comid,goods.isgroup as isgroup ';
        $sql .= ' from `' . sh . '_comgoods` as comgoods  ';
        $sql .= ' left join `' . sh . '_goods` as goods  '; //join 查询 去取名称等信息
        $sql .= ' on comgoods.goodsid=goods.id ';
        $sql .= ' where 1 ';
        $sql .= ' and comgoods.comid=:comid ';

        $this->j['list'] = $this->pdo->fetchAll($sql, Array(':comid' => $this->comid));
    }

    /* 删除店铺商品 */

    function dodel() {
        $id = $this->main->rqid();

        /* 检测有没有柜门在卖这种商品 */
        $sql = 'select count(*) from `' . sh . '_door` d left join `' . sh . '_comgoods` cg on d.comgoodsid=cg.id';
        $sql .= ' where cg.id=:id';
        $sql .= ' and cg.comid=:comid and d.hasgoods=1';
        $rs = $this->pdo->counts($sql, Array(':id' => $id, ':comid' => $this->comid));
        if ($rs > 0) {
            $this->ckerr('此商品正在售卖，不可删除');
            return;
        }

        /*检测是否有组合品包含这个单品*/
        if($this->grouphasme($id, $this->comid)){
            $this->ckerr('有组合品包含这个单品,请先删除组合品');
            return;
        }
        
        
        $sql = 'delete from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and comid=:comid';
        $sql .= ' and id=:id';

        $para[':id'] = $id;
        $para[':comid'] = $this->comid;

        $this->pdo->doSql($sql, $para);

        $this->j['success'] = 'y';
    }

    /* 出售某商品,把平台商品添加到酒店商品 */

    function dosell() {
        $id = $this->main->rqid();

        /* 提取出平台商品 */
        $sql = 'select * from `' . sh . '_goods` where 1 ';
        $sql .= ' and id=:id';

        $a_goods = $this->pdo->fetchOne($sql, Array(':id' => $id));

        if (false == $a_goods) {
            $this->ckerr('没找到平台对应商品');
        }

        /* 如果是组合品，检测店铺是不是添加过这些组合品的单品 */
        if (!$this->hasmysingle($a_goods, $this->comid)) {
            $this->ckerr('添加组合品前请先添加这个组合品的单品');
            return false;
        }


        /* 检测有没有这种商品 */
        $sql = 'select count(*) from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and goodsid=:goodsid';
        $sql .= ' and comid=:comid ';
        $counts = $this->pdo->counts($sql, Array(':goodsid' => $id, ':comid' => $this->comid));
        if ($counts > 0) {
            $this->ckerr('已经添加过这种商品了');
        }

        /* 把平台商品添加进店铺商品 */
        $rs['goodsid'] = $a_goods['id'];
        $rs['goodsic'] = $a_goods['ic'];
        $rs['price'] = 0;
        $rs['comid'] = $this->comid;


        $this->pdo->insert(sh . '_comgoods', $rs);

        $this->j['success'] = 'y';
    }

    /* 提取平台的全部商品不包括已经在卖的 */

    function selectgoods() {
        $sql = 'select * from `' . sh . '_goods` where 1 ';

        $sql .= ' and id not in(Select goodsid from `' . sh . '_comgoods` where 1 and comid=:comid) '; //不在店铺自营商品里

        $this->j['list'] = $this->pdo->fetchAll($sql, Array(':comid' => $this->comid));
    }

    /* 保存价格 */

    function saveprice() {
        $id = $this->main->rqid();

        $price = $this->main->request('price', '价格', 0, 500, 'num');
        $commission = $this->main->request('commission', '价格', 0, 500, 'num');

        $this->ckerr();

        $price *= 100; //将收到的价格*100，以分为单位存入数据库
        $commission *= 100;

        if ($commission > $price) {
            $this->ckerr('佣金不可大于商品价格');
        }


        /* 检测不能有小数点 */
        if (strpos($price, '.') OR strpos($commission, '.')) {
            $this->ckerr('价格或佣金小数点不能超过三位');
        }




        $rs['price'] = $price;
        $rs['commission'] = $commission;


        $this->pdo->update(sh . '_comgoods', $rs, 'id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    function formalarm() {
        $id = $this->main->rqid('id');

        $data = $this->getgoods($id);

        $this->j['data'] = $data;
    }

    function savealarm() {
        $id = $this->main->rqid('id');


       
          $this->main->posttype = 'post';
           $inventoriesalarm =  $this->main->request('inventoriesalarm', '警戒库存', 0, 9999999, 'int');
       // $inventoriesalarm = $this->main->rfid('inventoriesalarm');
  if(!$this->ckerr())
  {
            return false;
  }

       
        $sql = 'update `' . sh . '_comgoods` set inventoriesalarm=' . $inventoriesalarm;
        $sql .= ' where id=' . $id;

        $this->pdo->doSql($sql);

        $this->j['success'] = 'y';
    }

    function getgoods($goodsid) {
        $sql = 'select * from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and id=:goodsid';

        return $this->pdo->fetchOne($sql, Array(':goodsid' => $goodsid));
    }

    function toplat() {
        $id = $this->main->rqid('id');

        /* get商品名称，剩余量 */
        $sql = 'select goods.title as title ';
        $sql .= ' ,comgoods.inventories as inventories ';
        $sql .= ' from `' . sh . '_comgoods` as comgoods ';
        $sql .= ' left join `' . sh . '_goods` as goods on comgoods.goodsid=goods.id ';
        $sql .= ' where comgoods.id=:id';

        $para[':id'] = $id;

        $result = $this->pdo->fetchOne($sql, $para);

        $this->j['data'] = $result;
    }

    /* 执行退回平台 */

    function dotoplat() {
        $id = $this->main->rqid('id');

        $this->main->posttype = 'post';
        $formcode = $this->main->request('formcode', '凭证号', 1, 20, 'char', 'invalid');
        $mycount = $this->main->request('mycount', '数量', 1, 999999, 'int');
        if (!$this->ckerr()) {
            return false;
        }
        /* 检测退回量不能大于剩余量 */
        $sql = 'select goodsid, inventories, comid from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and id=:id';
        $para[':id'] = $id;
        $a_comgoods = $this->pdo->fetchOne($sql, $para);

        if ($mycount > $a_comgoods['inventories']) {
            $this->ckerr('退回量不能大于剩余量');
            return false;
        }

        /* 提取店铺 */
        $sql = 'select * from `' . sh . '_com` where 1 ';
        $sql .= ' and id=:comid';
        unset($para);
        $para['comid'] = $a_comgoods['comid'];
        $a_com = $this->pdo->fetchOne($sql, $para);

        /* 执行退回 */
        $pdo = & $GLOBALS['pdo'];

        try {
            $pdo->begintrans();

            $currenttime = time();

            /* 添加记录 */
            $rs['goodsid'] = $a_comgoods['goodsid'];
            $rs['formcode'] = $formcode;

            $rs['mycount'] = $mycount;
            $rs['mytype'] = 'toplat';
            $rs['other'] = '';

            $rs['comid'] = $a_com['id'];
            $rs['comic'] = $a_com['ic'];
            $rs['comname'] = $a_com['title'];


            $rs['duid'] = $this->main->user['id'];
            $rs['dname'] = $this->main->user['u_fullname'];
            $rs['stime'] = date('Y-m-d H:i:s');
            $rs['stimeint'] = $currenttime;

            $storeid = $this->pdo->insert(sh . '_store', $rs);

            /* 更新统计 */
            $sql = 'update `' . sh . '_goods` set ';
            $sql .= ' inventories=inventories+' . $mycount;
            $sql .= ' where id=' . $a_comgoods['goodsid'];
            $this->pdo->doSql($sql);

            /* 更新店铺商品统计 */
            $sql = 'update `' . sh . '_comgoods` set ';
            $sql .= ' inventories=inventories-' . $mycount;
            $sql .= ' where id=' . $id;

            $this->pdo->doSql($sql);

            /* 添加店铺仓库记录, 对店铺是入库 */
            unset($rs);
            $rs['goodsid'] = $a_comgoods['goodsid'];
            $rs['comgoodsid'] = $id;
            $rs['formcode'] = $formcode;
            $rs['mycount'] = $mycount;
            $rs['mytype'] = 'toplat'; //
            $rs['comid'] = $a_com['id'];


            $rs['duid'] = $this->main->user['id'];
            $rs['dname'] = $this->main->user['u_fullname'];
            $rs['stime'] = date('Y-m-d H:i:s');
            $rs['stimeint'] = $currenttime;

            $this->pdo->insert(sh . '_comstore', $rs);

            $pdo->submittrans();
        } catch (PDOException $e) {

            $pdo->rollbacktrans();
            echo ($e);
            die();
        }

        $this->j['success'] = 'y';
    }

    /*本身就是单品,或已经加过这个组合品的单品了返回true*/
    function hasmysingle(&$a_goods, $comid) {
        if (0 == $a_goods['isgroup']) {
            return true;
        }

        $mygroup = $a_goods['mygroup'];

        $a_mygroup = json_decode($mygroup, true);
        
        foreach($a_mygroup as $v){
            $goodsid = $v['id'];
            
            $sql = 'select count(*) from `'.sh.'_comgoods` where 1 ';
            $sql .= ' and goodsid=:goodsid';
            $sql .= ' and comid=:comid';
          
            unset($para);
            
            $para[':goodsid'] = $goodsid;
            $para[':comid'] = $comid;
    
            $counts = $this->pdo->counts($sql, $para);
            
            if($counts<1){
                return false;
            }
        }
        
        return true;
    }
    
    /*查是否有组合品包含这个单品*/
    function grouphasme($comgoodsid, $comid){
        /*get要删除的这个店铺商品*/
        $sql = 'select * from `'.sh.'_comgoods` where 1 ';
        $sql .= ' and id=:comgoodsid';
        $para[':comgoodsid'] = $comgoodsid;
        
        $a_comgoods = $this->pdo->fetchOne($sql, $para);

        if(false == $a_comgoods){
            $this->ckerr(1022);
            return false;
        }
        
                
        /*提取所有包含这个单品的组合*/
        $sql = 'select id from `'.sh.'_goods` where 1 ';
        $sql .= ' and mygroup is not null ';
        $sql .= ' and mygroup like :id';
        $a_groupgoods = $this->pdo->fetchAll($sql, Array(':id'=>'%"id":"'.$a_comgoods['goodsid'].'"%'));

        /*没有包含这个商品的组合就不用再查了*/
        if(false == $a_groupgoods){
            return false;
        }
        
        /*最后检查店铺是不是卖了这个组合品*/
        $groupids = join(',', array_column($a_groupgoods, 'id'));
   
        $sql = 'select count(*) from `'.sh.'_comgoods` where 1 ';
        $sql .= ' and comid=:comid ';
        $sql .= ' and goodsid in('.$groupids.')';
        $counts=$this->pdo->counts($sql, Array(':comid'=>$comid));
        
        if($counts>0){
            return true;
        }else{
            return false;
        }
        
        
        
    }

}

$myapi = new myapi(); //建立类的实例
unset($myapi); //释放类占用的资源

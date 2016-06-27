<?php

/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
require_once ApiPath . '_adminxxx1/_main.php';
/* 返回用户组 */

class admin_business_company extends cls_api {

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
            case 'creat':
                //$this->myform();
                $this->output();
                break;
            case 'edit':
                $this->getdata();
                $this->output();
                break;
            case 'admin': //管理用户
                $this->getdata();
                $this->output();
                break;

            case 'isrun':
            case 'unrun':
            case 'islock':
            case 'unlock':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->doadmin();
                $this->output();
                break;
            case 'nsave': //保存新用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savenew();
                $this->output();
                break;
            case 'esave':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->esave();
                $this->output();
                break;
            case 'savepass':
                $_POST['outtype'] = 'json'; //输出json格式
                $this->savepass();
                $this->output();
                break;
            case 'del': //删除用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->del();
                $this->output();
        }
    }

    /* 用户列表 */

    function mylist() {
        $sql = 'select * from `' . sh . '_com` where 1 ';
        $sql .= ' order by id desc ';

        $result = $this->main->exers($sql, null);

        $this->j['list'] = $result;
    }

    /* function myform(){ 
      $sql = 'select distinct(uid) from `' . sh . '_com` where 1';
      $res = $this->main->executers($sql);
      $str = array_reduce($res, function($rs,$v){
      return $v['uid'].','.$rs;
      });
      $str = substr($str, 0,-1);
      $sql = 'select u_name from `' .sh. '_user` where id not in ('.$str.') and u_gic="bizer"';
      $e = $this->main->executers($sql);
      $this->j['comname'] = $e;
      //stop($e,true);
      } */

    /* 保存用户 */

    function esave() {
        $we = & $GLOBALS['main'];

        $id = $we->rfid();

        $we->posttype = 'post';


        $rs['ic'] = $we->request('ic', '编码', 2, 20, 'char', 'invalid');
        $rs['title'] = $we->request('title', '店铺名称', 2, 20, 'char', 'encode');
        $rs['preimg'] = $we->request('preimg', '预览图', 2, 255, 'char', 'encode');
        $rs['mylocation'] = $we->request('mylocation', '地址', 2, 50, 'char', 'encode');
        $rs['telfront'] = $we->request('telfront', '前台电话', 8, 20, 'char', 'encode');
        $rs['a_name'] = $we->request('a_name', '开户名', 2, 20, 'char', 'encode');
        $rs['a_bank'] = $we->request('a_bank', '开户行', 2, 20, 'char', 'encode');
        $rs['a_number'] = $we->request('a_number', '银行账户', 2, 25, 'char', 'encode');

        $this->ckerr();

        //检测店铺名称
        if ($this->main->hasname(sh . '_com', 'title', $rs['title'], $id)) {
            $this->ckerr('此店铺名称已经存在，请重新输入', 'title');
        }

        /* 检测重复ic */
        if ($we->hasic(sh . '_com', $id, $rs['ic'])) {
            $this->ckerr('编码重复', 'ic');
        };




        $id = $this->pdo->update(sh . '_com', $rs, 'id=:id', Array(':id' => $id));

        /* 更新其它地方，用编码的地方 */

        $this->j['success'] = 'y';
    }

    function savenew() {
        $we = & $GLOBALS['main'];

        $we->posttype = 'post';

        $u_name = $we->request('u_name', '商家用户名', 6, 20, 'char', 'invalid');
        $rs['ic'] = $we->request('ic', '编码', 2, 20, 'char', 'invalid');
        $rs['title'] = $we->request('title', '店铺名称', 2, 20, 'char', 'encode');
        $rs['preimg'] = $we->request('preimg', '预览图', 2, 255, 'char', 'encode');
        $rs['mylocation'] = $we->request('mylocation', '地址', 2, 50, 'char', 'encode');
        $rs['telfront'] = $we->request('telfront', '前台电话', 8, 13, 'char', 'encode');
        $rs['a_name'] = $we->request('a_name', '开户名', 2, 20, 'char', 'encode');
        $rs['a_bank'] = $we->request('a_bank', '开户行', 2, 20, 'char', 'encode');
        $rs['a_number'] = $we->request('a_number', '银行账户', 2, 30, 'char', 'encode');

        $this->ckerr();
        //检测店铺名称
        if ($this->main->hasname(sh . '_com', 'title', $rs['title'])) {
            $this->ckerr('此店铺名称已经存在，请重新输入', 'title');
        }

        /* 检测重复ic */
        if ($we->hasic(sh . '_com', -1, $rs['ic'])) {
            $this->ckerr('编码重复', 'ic');
        };

        /* 按用户名提取商家信息 */
        $sql = 'select * from `' . sh . '_user` where 1 ';
        $sql .= ' and u_gic="bizer" ';
        $sql .= ' and u_roleic="sys" ';
        $sql .= ' and u_name=:u_name';

        $result = $this->pdo->fetchOne($sql, Array(':u_name' => $u_name));

        if (false == $result) {
            $this->ckerr('没找到这个用户');
        }
        $rs['uid'] = $result['id'];
        $rs['u_name'] = $result['u_name'];
        $rs['u_nick'] = $result['u_nick'];


        /* 默认信息 */
        $rs['stimeint'] = time();
        $rs['suid'] = $we->user['id'];
        $rs['snick'] = $we->user['u_nick'];

        $rs['hits'] = 0;
        $rs['cls'] = '9999999';
        $rs['isrun'] = 0;
        $rs['islock'] = 0;



        try {
            $this->pdo->begintrans();

            $id = $this->pdo->insert(sh . '_com', $rs);
            
            /*更新用户的comid,为刚添加的这个店铺的id*/
            $sql = 'update `'.sh.'_user` set comid='.$id.' where 1 ';
            $sql .= ' and id='.$result['id'];
            
            $this->pdo->doSql($sql);

            /* 再建个统计账号 */
            unset($rs);
            $rs['uid'] = $result['id'];
            $rs['comid'] = $id;
            $rs['unick'] = $result['u_nick'] . '';
            $rs['mytype'] = 'biz';
            $id = $this->pdo->insert(sh . '_account', $rs);

            $this->pdo->submittrans();
        } catch (PDOException $e) {
            $this->pdo->rollbacktrans();
            echo ($e);
            die();
        }


        $this->j['success'] = 'y';
    }

    function del() {
        $id = $this->main->rqid();

        /* 检测不能删除有商品的商家 --  */
        $sql = 'select count(*) from `' . sh . '_comgoods` where 1 ';
        $sql .= ' and comid=:id';

        $counts = $this->pdo->counts($sql, Array(':id' => $id));

        if ($counts > 0) {
            $this->ckerr('不能删除有商品的店铺');
        }

        /* 检测不能删除有商品的商家 -- 自营商品 */
        $sql = 'select count(*) from `' . sh . '_goods` where 1 ';
        $sql .= ' and comid=:id';

        $counts = $this->pdo->counts($sql, Array(':id' => $id));

        if ($counts > 0) {
            $this->ckerr('不能删除有商品的店铺');
        }



        /* 不能删除有设备的店铺 */
        $sql = 'select count(*) from `' . sh . '_device` where 1 ';
        $sql .= ' and comid=:id';
        $counts = $this->pdo->counts($sql, Array(':id' => $id));
        if ($counts > 0) {
            $this->ckerr('不能删除设备的店铺，请先把设备移出店铺');
        }

        /* 检测有没有管理用户 */
        $sql = 'select count(*) from `' . sh . '_user` where 1 ';
        $sql .= ' and u_gic="bizer" ';
        $sql .= ' and u_roleic<>"sys" ';
        $sql .= ' and comid=:id';
        $counts = $this->pdo->counts($sql, Array(':id' => $id));
        if ($counts > 0) {
            $this->ckerr('不能删除有操作员的店铺，请先删除操作员');
        }


        /* 执行删除 */
        $sql = 'delete from `' . sh . '_com` where 1 ';
        $sql .= ' and id=:id';

        $this->pdo->doSql($sql, Array(':id' => $id));

        /* 删除这个店铺的统计信息 */
        $sql = 'delete from `' . sh . '_account` where 1 ';
        $sql .= ' and comid=:id';
        $sql .= ' and mytype="biz"';

        $this->pdo->doSql($sql, Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    /* 对用户的各种操作 */

    function doadmin() {
        $id = $this->main->rqid();

        /* 提店铺信息 */
        $sql = 'select * from `' . sh . '_com` where 1 ';
        $sql .= ' and id=:id ';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $rs = Array();

        switch ($this->act) {
            case 'isrun':
                if (1 == $result['isrun']) {
                    $this->ckerr('已经是运行状态了，不需要重复操作');
                } else {
                    $rs['isrun'] = 1;
                }
                break;
            case 'unrun':
                if (0 == $result['isrun']) {
                    $this->ckerr('已经是停止状态了，不需要重复操作');
                } else {
                    $rs['isrun'] = 0;
                }
                break;
            case 'islock':
                if (1 == $result['islock']) {
                    $this->ckerr('已经设为锁定了，不需要重复操作');
                } else {
                    $rs['islock'] = 1;
                }
                break;
            case 'unlock':
                if (0 == $result['islock']) {
                    $this->ckerr('已经设为解锁了，不需要重复操作');
                } else {
                    $rs['islock'] = 0;
                }
                break;
        }

        $this->pdo->update(sh . '_com', $rs, 'id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    /* 取店铺信息 */

    function getdata() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_com` where 1 ';
        $sql .= ' and id=:id';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $this->j['data'] = $result;
    }

}

$admin_business_company = new admin_business_company(); //建立类的实例
unset($admin_business_company); //释放类占用的资源
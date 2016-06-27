<?php
/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_user.php';
require_once ApiPath . '_adminxxx1/_main.php';
/* 返回用户组 */
class admin_business_repair extends cls_api {

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
            case 'fix':
                $_POST['outtype'] = 'json';
                $this->fixxing();
                $this->output();
                break;
         
            case 'del': //删除用户组
                $_POST['outtype'] = 'json'; //输出json格式
                $this->del();
                $this->output();
                break;
            case 'finish':
                $_POST['outtype'] = 'json';
                $this->finish();
                $this->output();
                break;
        }
    }

   function mylist() {
       /* 接收参数 */
       
        $this->main->posttype = 'get';
        $mytype = $this->main->request('mytype', '类型', 1, 10, 'char', 'invalid', false);
       //$comic = $this->main->request('comic', '店铺编码', 1, 50, 'char', '', false);
       $comname = $this->main->request('comname', '店铺名称', 1, 50, 'char', '', false);
       $deviceic = $this->main->request('deviceic', '设备ic', 1, 50, 'char', '', false);
        $dates = $this->main->getdates(strtotime(date('Y-m-d', (time() - 7 * 24 * 3600))), strtotime(date('Y-m-d', time())));
        
        
       /* 传回前端 */
        $this->j['search']['deviceic'] = $deviceic;
        $this->j['search']['comname'] = $comname;
        $this->j['search']['mytype'] = $mytype;
        $this->j['search']['date1'] = $dates['date1'];
        $this->j['search']['date2'] = $dates['date2'];
 
        if (!$this->ckerr()) {
            return false;
        }

        /* check com */
        
        if ('' != $comname) {
            $sql = 'select id from `' . sh . '_com` where title like :comname ';
            $result = $this->pdo->fetchAll($sql, Array(':comname' => '%' . $comname . '%'));
            if (false == $result) {
                $this->ckerr('没找到这个店铺');
                return false;
            } 
        }
        
        /* 处理参数 */
        if ('' == $mytype) {
            $mytype = 'all';
        }

        $this->j['search']['mytype'] = $mytype;
        
        
        $para = Array(); 
        
        $sql = 'select failtofix.*,com.title from `' . sh . '_failtofix` as failtofix';
        $sql .= ' left join `' . sh . '_com` as com on failtofix.comid=com.id ';
        $sql .= ' left join `' . sh . '_device` as device on failtofix.deviceic=device.ic ';
        $sql .= ' where 1 ';
        


        /* ic不为空时 加条件提数据 */
        if ('' !== $deviceic) {
            $sql .= ' and device.ic like :deviceic';
            $para[':deviceic'] = '%' . $deviceic . '%';
        }
        
        
        
        
        /* 为all时 加条件提数据 */
        if ('all' !== $mytype) {
            $sql .= ' and failtofix.mytype =:mytype ';
            $para[':mytype'] = $mytype;
        }
        
        
         /* 搜店铺 */
        if ('' != $comname) {
               $sql .= ' and com.title like :comname ';
               $para[':comname'] = '%' . $comname . '%';
          
        }
        
        /* date */
        $sql .= ' and failtofix.stimeint>=:date1_int';
        $sql .= ' and failtofix.stimeint<=:date2_int';

        $para[':date1_int'] = $dates['int1'];
        $para[':date2_int'] = $dates['int2'] + (24 * 3600);
         
        
        $sql .= ' and failtofix.isend=0 order by failtofix.status,failtofix.id desc';
        
        $result = $this->main->exers($sql, $para);

        $this->j['list'] = $result;
        
    }
   
   function fixxing(){ 
   	    $id = $this->main->rqid('id');
        $sql = 'update `'.sh.'_failtofix` set status=1,repairtime="'. date('Y-m-d H:i:s', time()).'" where id=:id';
        $rs = $GLOBALS['pdo']->doSql($sql,Array(':id'=>$id));
        $this->j['success']='y';
   }

   function finish(){ 
        $id = $this->main->rqid('id');
        $sql = 'update `'.sh.'_failtofix` set isend=1,repairtime="'. date('Y-m-d H:i:s', time()).'" where id=:id';
        $GLOBALS['pdo']->doSql($sql,Array(':id'=>$id));
        $this->j['success']='y';
   }

 }
    $admin_business_repair = new admin_business_repair(); //建立类的实例
    unset($admin_business_company); //释放类占用的资源

<?php

/* 购物车 */


require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . 'member/_main.php'; //用户后台通用数据


/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        $this->j = & $GLOBALS['j'];
        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
        $this->act = $this->main->ract();

        switch ($this->act) {
            case '':
                $this->pagemain();
                break;
        }
    }

    function pagemain() {
       $userid = $this->main->user['id'];
        $sql = 'select * ';
        $sql .= ' from   ' . sh . '_moneyuser  ';      
        $sql .= '  where uid=:uid';
        $sql .= ' order  by  id  desc';
        //print_r($sql);die;
        $resultmoneyuser = $this->pdo->fetchAll($sql, Array(':uid' => $userid));
        $GLOBALS['j']['list'] = $resultmoneyuser;
         if(! $this->ckerr() ) 
         {
             return;
         };
           
        $sql = 'select *';
        $sql .= ' from   ' . sh .  '_user ';       
        $sql .= '  where id=:uid';     
        $resultuser = $this->pdo->fetchOne($sql, Array(':uid' => $userid));
 
        $GLOBALS['j']['account'] = $resultuser;
       // print_r($resultuser);die;
        if(! $this->ckerr() ) 
         {
             return;
         };
        /* select moneyuser.*,users.ain,users.aout,users.acanuse from we_moneyuser as moneyuser,we_user as users
          where moneyuser.uid=users.id and users.id=2018
          order  by  moneyuser.stimeint desc 
        $userid = $this->main->user['id'];
        $sql = 'select moneyuser.*,users.ain,users.aout,users.acanuse';
        $sql .= ' from   ' . sh . '_moneyuser as moneyuser, ' . sh . '_user as users ';
        $sql .= ' where  moneyuser.uid=users.id ';
        $sql .= '  and users.id=:uid';
        $sql .= ' order  by  orders.stimeint desc';
        $result = $this->pdo->fetchAll($sql, Array(':uid' => $userid));
        $GLOBALS['j']['list'] = $result;
          */
        
    }

}

$myclassapi = new myapi();

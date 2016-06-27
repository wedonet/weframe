<?php

/* 店铺商品接口 */
require_once(__DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_biz.php';
require_once AdminApiPath . '_main.php';
require_once '_main.php'; /* 业务管理通用数据 */

/* */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
         $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
       
        $this->comid = $this->main->rid('comid');

        $c_company = new cls_companymain();
        
        /* 店铺信息添加进$globals['j']['company'] */
        $this->j['company'] = $c_company->getcompany($this->comid);
        
        $this->act = $this->main->ract();

        switch ($this->act) {
            case'';
                $this->mylist();
                $this->output();
                break;
           
           
            
        }
    }

       
    function mylist() {
        $c_biz = new cls_biz();
        $sql = 'select o.* ,p.uid_send ,p.uname_send ,p.uid_accept ,p.uname_accept ,p.time_send ,p.time_accept,p.type_send from `' . sh . '_order` as o ';
        $sql .= ' left join `'.sh.'_order_send` as p on o.id=p.orderid ';
        $sql .= ' where o.comid=:comid';
        $sql .= ' and  o.order_type=1';
        $sql .= ' order by id desc ';        
        $result = $this->main->exers($sql, Array(':comid'=>$this->comid));

        
        
        
        $this->j['list'] = $result;        
            if ('' == $this->act) {
                $i = 0;
                foreach ($result['rs'] as $v) {
                    $result['rs'][$i]['mystatusname'] = $c_biz->orderstatus[$v['mystatus']];                  
                    $i++;
                }
                $this->j['list'] = $result;
                return;
            }     
    }
   

    
}

$myapi = new myapi();//建立类的实例
unset($myapi);//释放类占用的资源
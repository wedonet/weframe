<?php
require_once(__DIR__.'/../../global.php');
require_once syspath.'_style/cls_template.php';
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_socket.php';
class myapi extends cls_api{
    function __construct() {
        parent::__construct();
        $this->init();
       switch ($this->act)
        {
            default :                
                $_POST['outtype']='json';
               unset($this->j['user']);
                $this->output();
               
                break;
        }
        }
        function init(){
        
          $deviceic=$this->main->request('deviceic','设备号',12,12,'char','',false);
          $deviceid=$this->main->rqid('deviceid');
          $comid=$this->main->request('comid','商家号',0,9999,'int','invalid',false);
          if(!empty($deviceic) && !empty($comid))
          {
              $sql = 'select * from '.sh.'_com as a,'.sh.'_place as b,' . sh . '_device as c where 1';
              $sql .= ' and a.id=b.comid';
              $sql .= ' and b.id=c.placeid';
              $sql .= ' and c.ic=:deviceic';
              $sql .= ' and a.id=:comid';
              
               $query=  array();
               $query[':deviceic']=$deviceic;
               $query[':comid']=$comid;
               
               $device = $GLOBALS['pdo']->fetchOne($sql,$query); 
            
               //echo($device);die;
               //print_r($device);die;
               if($device!=null)
               {
                  $this->mainapi($deviceic);
               }
           else {
               $GLOBALS['errmsg']='不存在此酒店设备';
                }
               
          }
     else {
          $GLOBALS['errmsg']='请输入设备号或商家号';
          }
            //有错误时返回错误信息
        if (!empty($GLOBALS['errmsg'])) {
            $xml['err'] = $GLOBALS['errmsg'];
            //stop($xml['err'],true);
            echo ( json_encode($xml) );
            die;
        }
        }
      function  mainapi($deviceic)
        {       
            $ip='192.168.0.183';
            $port='50601';
            $xml='';
            $xml['err']='';
            $xml['success']=0;          
            $s_socket=new cls_socket($ip,$port);
             $send = $s_socket->getsendstrs($deviceic);
           if ($s_socket->write($send)) {
               $this->j['success'] = 'y';
          
           } 
           else 
               {
               $this->j['success'] = 'n';
           }
           
        }
}
$myclassapi=new myapi();


<?php

/*扫购*/

/* 用户组接口 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_style/cls_template.php';

class myclassapi extends cls_template{
    function __construct() {
        $this->j =& $GLOBALS['j'];
        
        $this->act=$this->ract();
        
        switch ($this->act){
            case '':
                $this->main();
                break;
        }      
    }
  
   function main(){
        /*柜门状态*/
        $detail['doorstatus']='close';
    }
}

$myclassapi = new myclassapi();
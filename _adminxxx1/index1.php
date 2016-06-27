<?php

require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

require_once(adminpath . '/admin.php');





/*把数据给模板的数据全据变量j*/


class myclass extends cls_template{
    
    function __construct() {
		require_once(AdminApiPath.'_index.php');		
        require_once( adminpath . 'checkpower.php' ); //检测权限
 
        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mainframe'; //主内容区
                require_once( adminpath . 'main1.php' ); //主模板
                break;
            default:
                break;
        }
    }  
    
    function mainframe(){
     
        
        
     
    }

    
    
}

$tp = new myclass();
unset($tp);
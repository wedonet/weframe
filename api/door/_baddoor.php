<?php

/* 生成定单 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_socket.php';


//$main->write_file(syspath.'/api/door/request'.time().'.htm', pr());

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
       
        switch ($this->main->ract()) {
            default:
                $_POST['outtype'] = 'json';
                $this->main();
                $this->output();
                break;
        }
    }

	
	function main()	{
		
	} 
}


$myclassapi = new myapi();
<?php

/* 检测服务端是否掉线，掉线短信通知 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once syspath . '_inc/cls_socket.php';
require_once (syspath . '_inc/cls_sms.php');

class myapi extends cls_api {

    function __construct() {
        parent::__construct();


        switch ($this->act) {
            case '':
                $this->pagemain();
                break;
        }
    }

    function pagemain() {

			echo '<html>';
			echo '<title>监控汇川酒店服务器</title>';

			echo '<head><meta http-equiv="refresh" content="300"></head>';


			echo 'haha';

      
        $ip = '192.168.0.192';     

        $port = '50601';


        $c_socket = new cls_socket($ip, $port);


        $con = $c_socket->connect();


        set_time_limit(60000000);

		  for($i=1;$i<25;$i++){

			   $send = $c_socket->getsendstr('C89346C4DE16', $i);

            $c_socket->write($send);

				$sql = 'insert into we_huichuandoor (stime, doortitle) values ("'.date('Y-m-d H:i:s',time()).'", "'.$i.'")';
				$this->pdo->doSql($sql);
         
            /* 1000000 微秒 = 1秒 */
            usleep(4000000); 
       
		  }

		 
       
        $c_socket->close();
    }

}

$myapi = new myapi();
unset($myapi);

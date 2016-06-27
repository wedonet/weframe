<?php



function pr($type=false){
	$s = '';
	$s.= '<div style="width:600px;">'.PHP_EOL;
	$s.= '<table cellspacing="1" class="table1">'.PHP_EOL;
	$s.= '<tr>'.PHP_EOL;
	$s.= '<td>POST: ' .count($_POST). '</td>'.PHP_EOL;
	$s.= '<td>GET: ' .count($_GET). '</td>'.PHP_EOL;
	$s.= '</tr>'.PHP_EOL;
	$s.= '<tr valign="top">'.PHP_EOL;
	$s.= '<td>'.PHP_EOL;
	if($type=='json'){
			foreach ($_POST as $key=>$v ) {
				$t=json_decode($v);
				//$t=$v;
				$s.= str_replace( "\n", '<br />', ( print_r( $t, true )));
				$s.= '----'.$key.'<br/>';
			}
		//$s.= "new----".PHP_EOL;
		//$s.= str_replace( "\n", '<br />', ( print_r( $t, true )));
	}else{
		$s.= str_replace( "\n", '<br />', ( print_r( $_POST, true )));
	}

	//$t = str_replace( "\n", '<br />', ( print_r( $_POST, true )));
	/*foreach ($_POST as $key=>$value ) {
		$s.= '$'.$key .' = '. $value . '<br />'.PHP_EOL;
	}*/

	$s.= '</td>'.PHP_EOL;
	$s.= '<td>'.PHP_EOL;
	
	$t = str_replace( "\n", '<br />', ( print_r( $_GET, true )));
	$s .= $t;
	
	/*foreach ($_GET as $key=>$value ) {
		$s.= '$'.$key .' = '. $value . '<br />'.PHP_EOL;
	}*/

	$s.= '</td>'.PHP_EOL;
	$s.= '</tr>'.PHP_EOL;
	$s.= '</table>'.PHP_EOL;
	$s.= '<br /><br />'.PHP_EOL;

	return $s;
} // end func

function wpr(){
   echo pr();
   die;
   
}


function print_ra( $rs ){
    return str_replace( "\n", '<br />', ( print_r( $rs, true )));
} // end func  str_replace( "\n", '<br />', ( print_r( $rs, true )));

function mongolog($database,$table,$data=null){
	//创建链接
	$m = new MongoClient("mongodb://localhost:27000");
	$db = $m->$database;
	$collection = $db->$table;
	$document = array(
			"get" => $_GET,
			"post" => $_POST);
	if($data)$document=$data;
	$collection->insert($document);
	return true;
}





class t
{
	/**
	 * 临时文件，检查从cookie提密码情况
	 */
	function checkpass($rs, $u_pass)
		{
			print_r( '随机码是'.$rs['crandcode'] );

			echo ('----<br />');

			echo ('cookie数组是：<br />');

			print_r( unserialize($_COOKIE [CacheName . 'user']) );


			echo ('----<br />');
			print_r('cookie里存储的pass='.$u_pass);

			echo ('----<br />');

			echo 'md5后的字符是'.md5($rs['u_pass'] . $rs['crandcode']);

			echo '<br />传进来的密码是'.$u_pass;
			die;
			stop( $u_pass );
	} 

} 

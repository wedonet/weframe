<?php

require_once('../global.php');
require_once('../_inc/cls_login.php');



$act = $we->ract();

switch ($act) {
	case '':
		?>
		<form method="post" action="?act=login">
				请输入用户名:<input type="text" name="u" value="" size="20" /> 
				<input type="submit" />
		</form>
		<?php
		break;
	case 'login'					: mainfun(); break;
}
san();

function mainfun(){
	$uid = $GLOBALS['we']->rqid('u');

	if ($uid>0) {
		
	}
	else {
		$u_name = $GLOBALS['we']->request('用户名', 'u', 'post', 'char', 4, 20, 'invalid');

		showerr();

		$sql = 'select * from `'.sheet.'_user` where u_name="'.$u_name.'" or u_mobile="'.$u_name.'" ';
		
		$rs = $GLOBALS['we']->exeone( $sql );

		if ( FALSE === $rs ) {
			showerr(1018);
		}
		else {
			$uid = $rs['id'];
			$u_pass = $rs['u_pass'];
		}
	}

	if ($GLOBALS['we']->chkuserlogin( $u_name, $u_pass, 1, 0, 'f')) {
		$sucmsg = '<li>提交成功</li>'.PHP_EOL;
		$sucmsg .= '<li><a href="../_user/member/">点击这里转到用户控制面板</a></li>'.PHP_EOL;
		$sucmsg .= '<li><a href="../_user/biz/index.php">点击这里转到商户控制面板</a></li>'.PHP_EOL;
		$sucmsg .= '<li><a href="/">返回首页</a></li>'.PHP_EOL;
		$sucmsg .= '<li><a href="' .admindir. 'admin_index.php">管理中心</a></li>'.PHP_EOL;
		
		echo $sucmsg;
	}
	else {
		showerr($GLOBALS['we']->errmsg);
	}
} // end func



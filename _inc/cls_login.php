<?php

/**
 * Short description.
 * 
 * @author  YilinSun
 * @version 1.0
 * @package main
 */
/**
 * 登录
 */
//require( sysdir . '_inc/cls_e_money.php');
function chklogin() {
	$we = & $GLOBALS['we'];

	$js = '';
	/* 检测验证码 */
	/* if (FALSE === $we->codeistrue()) {
	  ajaxerr('验证码错误!');
	  } */

	/* 接收参数 */
	$u_name = $we->request('手机', 'u_name', 'post', 'mobile', 11, 11, 'invalid');
	$u_pass = $we->request('密码', 'u_pass', 'post', 'char', 6, 20);

	$savecookie = $we->rfid('savecookie');
	/* 有错误返回 */
	if ('' !== $GLOBALS['errmsg']) {
		ajaxerr();
	}

	/* 检测用户名密码是否正确 */
	if ($we->chkuserlogin($u_name, $u_pass, $savecookie, 0, 'form', null, null, 'member')) {
        //$c_money = new cls_e_money();
        //$c_money->getemoney($we->user['id'],'mark');
		$we->getuserinfo();

		$sucmsg = '<li><a href="/">返回首页</a></li>' . PHP_EOL;

		/* 商家,用户进入各自的控制面板 */
		switch ($we->u_gic) {
			case 'bizer' :
				$sucmsg .= '<li><a href="' . webdir . '_user/biz">Go 控制面版</a></li>' . PHP_EOL;
				break;
			case 'member' :
				$sucmsg .= '<li><a href="' . webdir . '_user/member">个人中心</a></li>' . PHP_EOL;
				break;
			default :
				break;
		}
		if(isset($_SESSION['comeurl'])){
			/* 返回来时的网址 */
			$flag = false;
			foreach ($GLOBALS['config']['filter_url'] as $key => $value) {
				if (strpos($_SESSION['comeurl'], $value)) {
					 $flag = true;
				}
				
			}
			if (!$flag) {

				$comeurl = $_SESSION['comeurl'];

				unset($_SESSION['comeurl']);

				autolocate($comeurl, 5000);

				$sucmsg .= '<li><a href="' . $comeurl . '">5秒后返回来时的网址</a></li>' . PHP_EOL;
			}else{ 
			    $index = '/';
	            autolocate($index, 2000);
			}
		}
		/* 管理员显示进入管理中心的链接 */
		if (1 == $we->user['u_iswebmaster']) {
			$sucmsg .= '<li><a href="' . admindir . 'admin_login.php">进入网站管理中心</a></li>' . PHP_EOL;
		}

		/* 不让关闭弹出窗口了，移除关闭按钮 */
		$sucmsg .= removeclose();

		ajaxinfo($sucmsg);
	} else {

		ajaxerr();
	}
}


/*商家登录*/
function chkloginbiz() {
	$we = & $GLOBALS['we'];
	
	$u_gic = 'bizer';
	$js = '';
	/* 检测验证码 */
	/* if (FALSE === $we->codeistrue()) {
	  ajaxerr('验证码错误!');
	  } */

	/* 接收参数 */
	$u_name = $we->request('用户名', 'u_name', 'post', 'char', 2, 50, 'invalid');
	$u_pass = $we->request('密码', 'u_pass', 'post', 'char', 5, 20);

	$savecookie = $we->rfid('savecookie');

	/* 有错误返回 */
	if ('' !== $GLOBALS['errmsg']) {
		ajaxerr();
	}

	



	/* 检测用户名密码是否正确 */
	if ($we->chkuserlogin($u_name, $u_pass, $savecookie, 0, 'form', null, null, $u_gic)) {

		$we->getuserinfo();

		$sucmsg = '<li><a href="/">返回首页</a></li>' . PHP_EOL;

		/* 商家,用户进入各自的控制面板 */
		switch ($we->u_gic) {
			case 'bizer' :
				$sucmsg .= '<li><a href="' . webdir . '_user/biz">Go 控制面版</a></li>' . PHP_EOL;
				break;
			default :
				break;
		}

		/* 返回来时的网址 */
		if (isset($_SESSION['comeurl'])) {

			$comeurl = $_SESSION['comeurl'];

			unset($_SESSION['comeurl']);
			autolocate($comeurl, 2000);

			$sucmsg .= '<li><a href="' . $comeurl . '">2秒后返回来时的页面</a></li>' . PHP_EOL;
		}

	

		/* 不让关闭弹出窗口了，移除关闭按钮 */
		$sucmsg .= removeclose();

		ajaxinfo($sucmsg);
	} else {
		ajaxerr();
	}
}


/*酒店软件商家登录*/
function chkloginsoftware() {
	$we = & $GLOBALS['we'];
	
	$u_gic = 'bizer';
	$js = '';
	/* 检测验证码 */
	/* if (FALSE === $we->codeistrue()) {
	  ajaxerr('验证码错误!');
	  } */

	/* 接收参数 */
	$u_name = $we->request('用户名', 'u_name', 'post', 'char', 2, 50, 'invalid');
	$u_pass = $we->request('密码', 'u_pass', 'post', 'char', 5, 20);

	$savecookie = $we->rfid('savecookie');

	/* 有错误返回 */
	if ('' !== $GLOBALS['errmsg']) {
		ajaxerr();
	}

	



	/* 检测用户名密码是否正确 */
	if ($we->chkuserlogin($u_name, $u_pass, $savecookie, 0, 'form', null, null, $u_gic)) {

		$we->getuserinfo();

		$url = webdir . '_user/software/';

		autolocate($url, 2000);

		
	} else {
		ajaxerr();
	}
}

/*管理员第一次登录*/
function chkloginadmin() {
	$we = & $GLOBALS['we'];

	$js = '';
	/* 检测验证码 */
	/* if (FALSE === $we->codeistrue()) {
	  ajaxerr('验证码错误!');
	  } */

	/* 接收参数 */
	$u_name = $we->request('用户名', 'u_name', 'post', 'char', 2, 50, 'invalid');
	$u_pass = $we->request('密码', 'u_pass', 'post', 'char', 5, 20);

	$savecookie = $we->rfid('savecookie');

	/* 有错误返回 */
	if ('' !== $GLOBALS['errmsg']) {
		ajaxerr();
	}

	$u_gic = array('supperadmin','editor','admin','service','market','channel','financial','financial_executive','operation','operation_director','testing');

	/* 检测用户名密码是否正确 */
	if ($we->chkuserlogin($u_name, $u_pass, $savecookie, 0, 'form', null, null)) {

		$we->getuserinfo();

		$sucmsg = '<li><a href="/">返回首页</a></li>' . PHP_EOL;

		/* 返回来时的网址 */
		if (isset($_SESSION['comeurl'])) {

			$comeurl = $_SESSION['comeurl'];

			unset($_SESSION['comeurl']);
			autolocate($comeurl, 2000);

			$sucmsg .= '<li><a href="' . $comeurl . '">2秒后返回来时的网址</a></li>' . PHP_EOL;
		}

		/* 管理员显示进入管理中心的链接 */
		if (1 == $we->user['u_iswebmaster']) {
			$sucmsg .= '<li><a href="' . admindir . 'admin_login.php">进入网站管理中心</a></li>' . PHP_EOL;
		}

		/* 不让关闭弹出窗口了，移除关闭按钮 */
		$sucmsg .= removeclose();

		ajaxinfo($sucmsg);
	} else {
		ajaxerr();
	}
}

// end func

function chkloginajax() {
	$we = & $GLOBALS['we'];

	/* 检测验证码 */
	/* if (FALSE === $we->codeistrue()) {
	  ajaxerr('验证码错误!');
	  } */

	/* 接收参数 */
	$u_name = $we->request('手机', 'u_name', 'post', 'mobile', 11, 11, 'invalid');
	$u_pass = $we->request('密码', 'u_pass', 'post', 'char', 5, 20);

	ajaxerr();

	$savecookie = $we->rqid('savecookie');

	/* 检测用户名密码是否正确 */
	if ($we->chkuserlogin($u_name, $u_pass, $savecookie, 0, 'form', null, null, 'member')) {

		$we->getuserinfo();

		//$sucmsg = '<li><a href="/">返回首页</a></li>'.PHP_EOL;
		$s = '';
		$s .= '<script type="text/javascript">' . PHP_EOL;
		$s .= 'reloaduserinfo();' . PHP_EOL; //如果有工具栏，则重新载入登录后的信息
		$s .= '</script>' . PHP_EOL;

		echo $s;

		jsucclose();
	} else {
		ajaxerr();
	}
}

/* 小平台ajax登录 */

function chkajaxofbiz() {
	$we = & $GLOBALS['we'];

	/* 检测验证码 */
	if (FALSE === $we->codeistrue()) {
		ajaxerr('验证码错误!');
	}

	/* 接收参数 */
	$hotelid = $we->rqid('hotelid');
	$u_name = $we->request('手机', 'u_name', 'post', 'mobile', 11, 11, 'invalid');
	$u_pass = $we->request('密码', 'u_pass', 'post', 'char', 5, 20);

	ajaxerr();

	$savecookie = $we->rqid('savecookie');

	/* 检测用户名密码是否正确 */
	if ($we->chkuserlogin($u_name, $u_pass, $savecookie, 0, 'form', null, $hotelid, 'member')) {

		$we->getuserinfo();

		//$sucmsg = '<li><a href="/">返回首页</a></li>'.PHP_EOL;
		jsucok();
	} else {
		ajaxerr();
	}
}



// end func



// end func



// end func

function chkinfoemoney($uid){
    $sql = 'select isinfo,ischeckmail from `' . sheet . '_user` where id=? limit 1';
    $res = $GLOBALS['we']->exeone($sql,array($uid));
    if($res==false){
    	return false;
    }
    $rs['isinfo'] = $res['isinfo'];
    $rs['ischeckmail'] = $res['ischeckmail'];
    return $rs;
}


function hasgrade($v, $myid = '') {
	$sql = 'select count(*) from `' . sheet . '_growcfg` where typename=:typename';
	
	if ($myid !== '') {
		$sql .= ' and id<>' . $myid;
	}

	$para['typename'] = $v;

	if ($GLOBALS['we']->rscount($sql, $para) > 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}


// end func




// end func
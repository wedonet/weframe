<?php

/**
 * @base
 * @name sunyilin 
 */
ob_start();
header('Content-Type:text/html; charset=UTF-8');
//error_reporting(E_ALL & ~E_NOTICE);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Shanghai');
ini_set('date.timezone', 'Asia/Shanghai');


session_start();















/* setting - 直接定义的常量 */
define('syspath', str_replace("\\", "/", dirname(__FILE__) . '/'));



/*可以在单元测试里设置服务器路径*/
if(!defined('ConfigPath')){
    define('ConfigPath', 'config.php');
}

$config['isrun'] = true; //网址是否运行中，维护时改成false
$config['unisruntext'] = ''; //维护时的提示

$config['webname'] = 'light'; //网站名称
$config['webdir'] = '/';
$config['weburl'] = 'http://www.ejshendeng.com'; //网址



$config['adminname'] = '_adminxxx1'; //管理文件夹路径




/* 数据库部分 */
$config['Dbms'] = 'mysql'; //数据库类型 oracle 用ODI,对于开发者来说，使用不同的数据库，只要改这个，不用记住那么多的函数了
$config['DbHost'] = 'localhost'; //数据库主机名
$config['Dbname'] = 'hotelseller'; //使用的数据库
$config['Dbport'] = '3306';
$config['Dbuser'] = 'root'; //数据库连接用户名
$config['Dbpass'] = '123456'; //对应的密码

$config['sh'] = 'we'; //数据库中表的前缀




$config['MaxPage'] = 18;
$config['CheckUser'] = 0;
$config['LoginErr'] = 5; //每天允许最多5次输错密码


$config['MailFrom'] = 'service@soolg.com'; //发信用邮箱地址
$config['MailServerUserName'] = 'service@soolg.com'; //发信smtp用户名
$config['MailServerPassword'] = 'soolg72353'; //发信smtp密码
$config['MailSmtp'] = 'mail.soolg.com'; //发信smtp的IP地址
$config['MailSmtpPort'] = '25'; //端口
$config['MailFromName'] = 'WeDoNet';
$config['timestamp'] = 1;

$config['CacheName'] = 'seller';
$config['CacheType'] = 'text';
$config['apiname'] = 'api';

$congif['err'][1000] = '没有权限';
$config['err'][1018] = '没找到相应记录';
$config['err'][1022] = '非法操作';


//会员来源
$config['source']['reg'] = "大平台注册";
$config['source']['upload'] = "大平台导入";
$config['source']['biz'] = "商家添加";
$config['source']['admin'] = "大平台添加";

/*是否发送短信*/
$config['issendsms'] = 1;

$config['serverip']='192.168.0.248'; //柜门机服务器地址
$config['allowed_types']="jpg|gif|jpg|jpeg|png|bmp|jpe|tiff|tif";//允许上传类型 |分割 可以配置多种

require_once(ConfigPath); //可以在这里重写参数设置








/* ===========================================================
 * Do Define
 */




define('isrun', $config['isrun']);
define('webname', $config['webname']);
define('webdir', $config['webdir']);
define('weburl', $config['weburl']);

define('adminname', $config['adminname']);
//define('sysadmindir', sysdir . adminname . '/');
define('serverip', $config['weburl']);





define('Dbms', $config['Dbms']); //数据库类型 oracle 用ODI,对于开发者来说，使用不同的数据库，只要改这个，不用记住那么多的函数了
define('DbHost', $config['DbHost']); //数据库主机名
define('Dbname', $config['Dbname']); //使用的数据库
define('Dbuser', $config['Dbuser']); //数据库连接用户名
define('Dbpass', $config['Dbpass']); //对应的密码



define('MaxPage', $config['MaxPage']);

define('CheckUser', $config['CheckUser']);
define('LoginErr', $config['LoginErr']); //每天允许最多50次输错密码



define('MailFrom', $config['MailFrom']); //发信用邮箱地址
define('MailFromName', $config['MailFromName']); //发信用邮箱显示名称
define('MailServerUserName', $config['MailServerUserName']); //发信smtp用户名
define('MailServerPassword', $config['MailServerPassword']); //发信smtp密码
define('MailSmtp', $config['MailSmtp']); //发信smtp的IP地址
define('MailSmtpPort', $config['MailSmtpPort']); //端口


define('timestamp', $config['timestamp']);



define('pagestarttime', microtime(true));

define('admindir', webdir . adminname . '/');
define('adminpath', syspath . adminname . DIRECTORY_SEPARATOR);
define('Apiname', $config['apiname']);
define('ApiPath', syspath . Apiname . DIRECTORY_SEPARATOR);
define('AdminApiPath', syspath . Apiname . DIRECTORY_SEPARATOR . adminname . DIRECTORY_SEPARATOR);

define('CacheName', $config['CacheName']);
define('CacheType', $config['CacheType']);
define('ALLOWED_TYPES', $config['allowed_types']);

define('sh', $config['sh']);



/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');


$errmsg = array();
$errinput = array(); //错误编码
$security = null;
$errcode = 0; //错误编码， 1022=没有权限， 1018=没找到记录


$j = array(); //存全局数据
$j['server']['runtime'] = 0;
$j['server']['sqlquerynum'] = 0;

//$GLOBALS['j']['message']='几点设备维护，请别买了a';



function san() {
    unset($GLOBALS['main']);
}


/*
 * 建立类的实例. loadc (load class)
 * name = 实例名 
 * classname = 类名
 */

function LoadClass($VariableName, $ClassName, $MyPath = null) {
    if (!isset($GLOBALS[$VariableName])) {

        if (null !== $MyPath) {
              require_once(syspath . '_inc' . DIRECTORY_SEPARATOR . $MyPath);
        }

        $GLOBALS[$VariableName] = new $ClassName();
    }
}

/*报严重错误，并终止运行*/
function err($errmsg=null) {
    if(null != $errmsg ) {
        if(is_numeric($errmsg)){
            die( $GLOBALS['config']['err'][$errmsg] );
        }else{
            die($errmsg);
        }
    }
}

function stop($s = '',$arr=false) {
  if($arr==true){
        echo '<pre>';
    print_r($s);
    echo '<br />==================';
    die;
  }
    echo $s;
    echo '<br />==================';
    die;
}


function log_message($level = 'error', $message, $php_error = FALSE) {
    //static $_log;
    //if (config_item('log_threshold') == 0)
    //{
    //	return;
    //}
    //$_log =& load_class('Log');
    //$_log->write_log($level, $message, $php_error);
}

function is_really_writable($file) {
	// If we're on a Unix server with safe_mode off we call is_writable

	if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE) {
		return is_writable($file);
	}

	// For windows servers and safe_mode "on" installations we'll actually
	// write a file then read it.  Bah...
	if (is_dir($file)) {
		$file = rtrim($file, '/') . '/' . md5(mt_rand(1, 100) . mt_rand(1, 100));

		if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
			return FALSE;
		}

		fclose($fp);
		@chmod($file, DIR_WRITE_MODE);
		@unlink($file);
		return TRUE;
	} elseif (!is_file($file) OR ($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
		return FALSE;
	}

	fclose($fp);
	return TRUE;
}

function die2(){
    echo __LINE__;
    die;
}

require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'main.php');
require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'help.php');
require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'pdo.php');
require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'cache'.DIRECTORY_SEPARATOR.'Cache.php');

cachestart();
$cache = new ClsCache();

$pdo = new Cls_Pdo();
$main = new ClsMain();
<?php

function crumb($s) {
    $GLOBALS['html']->crumb .= ('<li>' . $s . '</li>' . PHP_EOL);
}


function mytitle($s)
{
	$GLOBALS['html']->mytitle = $s;
} // end func

// end func


function werr($s, $return= false ) {
    $GLOBALS['errmsg'] .= ('<li>' . $s . '</li>' . PHP_EOL);
	if ( true == $return ) {
		return true;
	}
}


/**
 * 向全局写入错误编码
 */
function werric($s)
{
     $GLOBALS['erric'] .= (','.$s.',');
} // end func


function showwerr($s){
	werr($s);
	return false;
}

function getfaultname($s) {
    $a[1018] = '没找到相应记录';
    $a[2004] = '验证码错误';
    $a[1022] = '非法操作';

    return $a[$s];
}

function sort_array(&$array, $keyid, $order = 'asc', $type = 'number') {  
    if (is_array($array)) { 
        $order_arr = array();
        foreach ($array as $k=>$val) {  
            $order_arr[$k] = $val[$keyid];  
        }  
        $order = ($order == 'asc') ? SORT_ASC : SORT_DESC;  
        $type = ($type == 'number') ? SORT_NUMERIC : SORT_STRING; 
        if(is_array($order_arr))
            array_multisort($order_arr, $order, $type, $array);  
    }  
}  
// end func

/**
 * 设断点
 */
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

// end func

/*function loadclass($classname, $mypath = null) {
    if (!isset($GLOBALS[$classname])) {

        if (null !== $mypath) {
            require $mypath;
        }

        $GLOBALS[$classname] = new $classname();
    }
}*/

/*
 * 建立类的实例. loadc (load class)
 * name = 实例名 
 * classname = 类名
 */

function loadc($name, $classname, $mypath = null) {
    if (!isset($GLOBALS[$classname])) {

        if (null !== $mypath) {

            $mypath = sysdir . '/_inc/' . $mypath;

            require_once $mypath;
        }

        $GLOBALS[$name] = new $classname();
    }
}


/*是否有全局报错*/
function haserr()
{
	if ( '' !== $GLOBALS['errmsg'] ) {
		return true;
	}
	else{
		return false;
	}
} // end func


/*存cookie
* 方便有统一的cookie入口做统计
*/
function scookie($name, $value, $time)
{
	setcookie(CacheName . $name, $value, $time, '/');
} // end func
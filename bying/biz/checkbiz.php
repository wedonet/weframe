<?php

/* 检测是否是商家，不是跳到登陆页 */
if (1000 == $GLOBALS['j']['errcode']) {


    if (isset($_POST['outtype']) AND 'json' == $_POST['outtype']) {
	die;

    } else {
        header( 'Location: '.webdir .'biz/service/login.php');  
        die;
    }
}























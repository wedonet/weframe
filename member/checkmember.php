<?php

/*检测是否会员，不是则跳到登录页*/





/* 没有权限跳转到登录页 */
if (1000 == $GLOBALS['j']['errcode']) {


    if (isset($_POST['outtype']) AND 'json' == $_POST['outtype']) {
	die;

    } else {
        header('Location: /service/login.php');
        die;
    }
}






















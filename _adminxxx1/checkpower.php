<?php

/* 管理后台检测权限 */
$access = str_replace(adminname, '', ltrim($_SERVER['PHP_SELF'], "/"));
$access = str_replace(".php", '', $access);
$access = ltrim($access, '/');
$querys = explode('&', $_SERVER['QUERY_STRING']);
if (!empty($_GET['act'])) {
    $access = $access . '/' . trim($_GET['act']);
}elseif(!empty ($_POST['act'])){
    $access = $access . '/' . trim($_POST['act']);
}
if(empty($_POST)){
//    echo $access;
}

if (1000 == $GLOBALS['j']['errcode']) {
    /* 返回json数据时直接停止 */
    if (isset($_POST['outtype']) AND 'json' == $_POST['outtype']) {
        die;
    } else {

        echo '<script>' . PHP_EOL;
        echo ' window.location.href="' . admindir . 'login.php";' . PHP_EOL;
        ;
        echo '</script>' . PHP_EOL;

        //header('Location: /_adminxxx1/login.php');
        die;
    }
}


    $u_roleic = $GLOBALS['j']['user']['u_roleic'];
    if ($u_roleic !== 'sys') {//sys是超级管理员 不做权限判断
    if (empty($u_roleic)) {//无法获取u_roleic判断失败 跳转
        if (isset($_POST['outtype']) AND 'json' == $_POST['outtype']) {
        die;
        }
        echo '没有权限1， 2秒后跳转首页,...';
        echo '<script>' . PHP_EOL;
        echo 'setTimeout(function(){location.href="' . admindir . 'index.php";},2000);';
        echo '</script>' . PHP_EOL;
        die;
    }
    $sql = "select access_id from " . sh . "_group where ic='$u_roleic'";
    $accessid = $GLOBALS['pdo']->fetchOne($sql);
    $accessid = trim(str_replace('|', ',', $accessid['access_id']), ',');
    if (empty($accessid)) {//获取不到权限id 判断失败 跳转
        if (isset($_POST['outtype']) AND 'json' == $_POST['outtype']) {
        die;
    }
        echo '没有权限2， 2秒后跳转首页,...';
        echo '<script>' . PHP_EOL;
        echo 'setTimeout(function(){location.href="' . admindir . 'index.php";},2000);';
        echo '</script>' . PHP_EOL;
        die;
    }
    $sql = "select name from " . sh . "_access where id in ($accessid)";
    $accesses = $GLOBALS['pdo']->fetchAll($sql);
    foreach ($accesses as $v) {
        $allow_access[] = $v['name'];
    }
    $GLOBALS['allow_access']=$allow_access;//赋值给global 以便在全局使用    
    if (!in_array($access, $allow_access)) {//当前操作名不在权限名列表中 判断失败 跳转
        if (isset($_POST['outtype']) AND 'json' == $_POST['outtype']) {
        die;
        }
        echo '没有权限3， 2秒后跳转首页,...';
        echo '<script>' . PHP_EOL;
        echo 'setTimeout(function(){location.href="' . admindir . 'index.php";},2000);';
        echo '</script>' . PHP_EOL;
        die;
    }
}


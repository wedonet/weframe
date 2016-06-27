<?php 
$j =& $GLOBALS['j'];
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
	    <title><?php if( array_key_exists('headtitle', $j) ) { 	echo $j['headtitle']; }?></title>

        <!--如下是手机端打开时的处理-->
        <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
        <meta name="apple-mobile-web-app-capable" content="yes" />    
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <meta name="format-detection" content="telephone=yes"/>
        <meta name="msapplication-tap-highlight" content="no" />

        
        <link rel="apple-touch-icon-precomposed" href="../touch-icon-iphone.png" />
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../touch-icon-ipad.png" />
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../touch-icon-iphone4.png" />
        
        
        <link rel="stylesheet" href="/_css/base.css?<?php echo timestamp?>" type="text/css"/>
        <link rel="stylesheet" href="/_css/main.css?<?php echo timestamp?>" type="text/css"/>
        <link rel="stylesheet" href="/_css/main-type.css?<?php echo timestamp?>" type="text/css"/>
        <link rel="stylesheet" href="/_css/plus.css?<?php echo timestamp?>" type="text/css"/>
        <link rel="stylesheet" href="/_css/mycontent.css?<?php echo timestamp?>" type="text/css"/>

        <script src="/_js/jquery-1.11.3/jquery.min.js?<?php echo timestamp?>"></script>
        <script src="/_js/main.js?<?php echo timestamp?>"></script>

<!--        <script src="/_js/codejuery.js"></script> -->
        <script src="/_js/bying.js?<?php echo timestamp?>"></script> 
        <script src="/_js/grayscale.js?<?php echo timestamp?>"></script>
        <script src="/_js/jquery.scrollLoading.js?<?php echo timestamp?>"></script>
        <?php
        if (function_exists('headplus')) {
            headplus();
        }
        ?>

    </head>
    <body>
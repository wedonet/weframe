<?php
$j = & $GLOBALS['j'];
$timestame = 1;
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>上传</title>

		<link rel="stylesheet" href="/_css/base.css?<?php echo $timestame?>" type="text/css" />
		<link rel="stylesheet" href="/_css/plus.css?<?php echo $timestame?>" type="text/css" />
	
		<link rel="stylesheet" href="upload.css?<?php echo $timestame?>" type="text/css" />

		<script src="/_js/jquery-1.11.3/jquery.min.js?<?php echo $timestame?>"></script>
		<script src="/_js/main.js?<?php echo $timestame?>"></script>
		<script src="upload.js?<?php echo $timestame?>"></script>

		<?php if (function_exists('headplus')) { headplus();}?>
	</head>
	<body>
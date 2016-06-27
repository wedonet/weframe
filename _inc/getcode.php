<?php
require('../global.php');

main();



function main(){
	$a[0]['k'] = '1+1=?';
	$a[0]['v'] = '2';

	$a[1]['k'] = '1*1=?';
	$a[1]['v'] = '1';

	$a[2]['k'] = '3*6=?';
	$a[2]['v'] = '18';

	$a[3]['k'] = '4*4=?';
	$a[3]['v'] = '16';

	$a[4]['k'] = '5+5=?';
	$a[4]['v'] = '10';

	$mykey = array_rand($a);

	$s = $a[$mykey]['k'];
        

	$_SESSION['codestr'] = $a[$mykey]['v'];
	
    echo $s;
} // end func
san();
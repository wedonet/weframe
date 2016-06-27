<?php
require_once( 'global.php');
require_once syspath . '_inc/cls_door.php';
//require_once syspath . 'api/terminal/_devicedata.php';
//echo date('Y-m-d H:i:s', 1460995200);

//echo date('Y-m-d H:i:s', 1461081600);
  /* 开门 */
             $rs['deviceic'] = 'C89346404765';
            //$rs['deviceic'] = 'C89346C4CFDC';
           // $rs['doortitle'] = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
     $rs['doortitle'] = array(1,2);
            $c_door = new cls_door($rs);
    // $de=new _devicedata();
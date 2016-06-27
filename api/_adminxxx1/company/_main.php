<?php

class cls_companymain{
    
    function getcompany($comid){
        $sql = 'select * from `'.sh.'_com` where 1 ';
        $sql .= ' and id=:comid ';
       
        $result = $GLOBALS['pdo']->fetchOne($sql, Array(':comid'=>$comid));
        return $result;
    }
}

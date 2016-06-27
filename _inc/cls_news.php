<?php

class cls_news {

	function __construct() {
		$this->we = $GLOBALS['we'];
		$this->html = $GLOBALS['html'];
	}

	/* 动态分类 */

	function newsclass() {
		$tli = $this->we->htm('classli', '_li');
		$tli = str_replace('{$href}', webdir . 'news/list.php?classid={$id}', $tli);
		
		$sql = 'select * from `'.sheet.'_class` where 1 ';
		$sql .= ' and cid=38  and isshow=1';
		$sql .= ' and (pid in (';
        $sql .= 'select id from `'.sheet.'_class` where cid=38  and isshow=1)';
        $sql .= ' or pid=0)';
        $sql .= ' order by treeid asc, id asc ';
// 		$rs = $this->we->executers($sql);
// 		stop($rs,true);
		
		$li = $this->we->repm($sql, $tli);
		
		return $li;
	}
}


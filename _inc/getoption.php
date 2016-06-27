<?php
class getoption {

    public $getstar;
    public $errmsg;

    function __construct() {
		$this->we = &$GLOBALS['we'];

        $starstatus['2'] = '二星';
		$starstatus['3'] = '三星';
		$starstatus['4'] = '四星';
        $starstatus['5'] = '五星';
		
		/*价格*/
		$pricestatus['0-150'] = '0-150';
		$pricestatus['150-300'] = '150-300';
		$pricestatus['300-500'] = '300-500';
		$pricestatus['500以上'] = '500以上';

        $this->starstatus = & $starstatus;
        $this->pricestatus = & $pricestatus;
    }
	
	function starname($k) {
        if (array_key_exists($k, $this->starstatus)) {
            return $this->starstatus[$k];
        } else {
            showerr('关键词错误!');
        }
    }
}
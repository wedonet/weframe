<?php 

class Cls_AdUser{
	public $businesstype; /*hotel,food...*/
	public $u_gid;
	public $u_gname;



	
	function mainfun()	{
		define('sp','admin/_admin_user');

		crumb('酒店用户管理');

		switch ( $this->businesstype ) {
			case 'hotel':
				$this->u_gid = 6;
				$this->u_gname = '商家';
				break;
		}

		$act = $GLOBALS['we']->ract();

		switch ($act) {
			case ''			: $this->mylist(); break;
			case 'creat'	: $this->myform( FALSE ); break;
		}
	} // end func

	
	function mylist(){			
		$h = $GLOBALS['we']->style(sp, 'main');
		$tli = $GLOBALS['we']->style(sp, 'li');

		$GLOBALS['html']->mytitle = '用户管理';
		
		$sql = 'select * from `' .sheet. '_user` where 1 ';
		$sql .= ' and u_gid in (Select id from '.sheet.'_group where typeid=0) order by id desc'; //只显示用户组的会员

		$li = $GLOBALS['we']->repm($sql, $tli, null, 0, true);

		$h = str_replace('{$li}', $li, $h); 
		
		$h = str_replace('{$pagelist}', $GLOBALS['we']->pagelist(), $h);

		$GLOBALS['html']->adhead();
		$GLOBALS['html']->crumbad();

		echo $h;

		$GLOBALS['html']->adfoot();
	} // end func

	
	function myform( $isedit ){
		$h = $GLOBALS['we']->style(sp, 'myform');

		if ( $isedit ) {
		
		}
		else{
			crumb('添加用户');

			$h = str_replace('{$fromunick}', $GLOBALS['we']->u_nick, $h);
			$h = $GLOBALS['we']->removemdbfield($h, sheet.'_user');
		}

		$GLOBALS['html']->dohtmlad($h);
	} // end func
}
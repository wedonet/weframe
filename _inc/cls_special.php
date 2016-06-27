<?php
/**
 * 频道专题管理
 *
 * @YilinSun
 * @version 1.0
 */

define('sp', 'admin/_admin_special');


class Cls_Special{

private $cid;
private $module; //模块,用于在一个频道下的多种分类方式
private $ii;

function __construct(){
	$this->cid = $GLOBALS['we']->rqid('cid');

	$channel = $GLOBALS['we']->getarr('channel', $this->cid);

	if (FALSE == $channel) {
	    showerr(1022);
	}

	//crumb('<a href="?cid='.$this->cid.'">'.$channel[$this->cid]['title'].'</a>');
	crumb($channel['title']);
	crumb('<a href="?cid='.$this->cid.'">专题管理</a>');

	$act = $GLOBALS['we']->ract();

	switch ($act) {
		case ''					: $this->Main(); break;
		case 'creat'			: $this->MyForm(FALSE); break;	
		case 'nsave'			: $this->SaveForm(FALSE); break;
		case 'del'				: $this->DoDel(); break;
		case 'edit'				: $this->MyForm(TRUE); break;
		case 'esave'			: $this->SaveForm(TRUE); break;

		case 'content'			: $this->FormContent();	break;
		case 'savecontent'		: $this->SaveContent();	break;
	}
}


function Main(){
	$h = $GLOBALS['we']->style(sp, 'main');
	$tli = $GLOBALS['we']->style(sp, 'li');

	$sql = 'select * from `'.sheet.'_special` order by cls asc,id asc';
	$li = $GLOBALS['we']->repm($sql, $tli);

	$h = str_replace('{$cid}', $this->cid, $h);
	$h = str_replace('{$li}', $li, $h);

	$GLOBALS['html']->adhead();
	$GLOBALS['html']->crumbad();
	echo $h;
	$GLOBALS['html']->adfoot();
} // end func


function MyForm($isedit){
	$h = $GLOBALS['we']->style(sp, 'myform');

	if ($isedit) {
		$id = $GLOBALS['we']->rqid('id');

		$h = str_replace('{$action}', '?act=esave&amp;cid='.$this->cid.'&amp;id='.$id, $h);

		$sql = 'select * from `'.sheet.'_special` where id='.$id;

		$h = $GLOBALS['we']->repm($sql, $h);
	}
	else {
		$h = str_replace('{$action}', '?act=nsave&amp;cid='.$this->cid, $h);
		$h = str_replace('{$cls}', '100', $h);

		$h = $GLOBALS['we']->removemdbfield($h, sheet.'_special');
	}


	$GLOBALS['html']->dohtmlad($h);	
} // end func

function SaveForm( $isedit ){
	//Get Input
	$rs['title'] = $GLOBALS['we']->request('名称', 'title', 'post', 'char', 1, 50, 'encode');
	$rs['tip'] = $GLOBALS['we']->request('提示', 'tip', 'post', 'char', 1, 50, 'encode', FALSE);
	$rs['readme'] = $GLOBALS['we']->request('说明', 'readme', 'post', 'char', 1, 200, 'encode', FALSE);

	$rs['mytitle'] = $GLOBALS['we']->request('Title', 'title', 'post', 'char', 1, 200, 'encode', FALSE);
	$rs['mykeywords'] = $GLOBALS['we']->request('Keywords', 'mykeywords', 'post', 'char', 1, 200, 'encode', FALSE);
	$rs['mydescription'] = $GLOBALS['we']->request('Description', 'mydescription', 'post', 'char', 1, 200, 'encode', FALSE);

	$rs['preimg'] = $GLOBALS['we']->request('图标', 'preimg', 'post', 'char', 10, 200, 'encode', FALSE);
		
	$rs['cls'] = $GLOBALS['we']->rfid('cls', 100);
	$rs['isgood'] = $GLOBALS['we']->rfid('isgood', 0);
	$rs['isopen'] = $GLOBALS['we']->rfid('isopen', 1);
	
	ajaxerr();
	
	//Save Input
	if ($isedit) {
		$id = $GLOBALS['we']->rqid('id');

		$GLOBALS['we']->pdo->update(sheet.'_special', $rs, ' id='.$id);
	}
	else {
		$rs['cid'] = $this->cid;
		$GLOBALS['we']->pdo->insert(sheet.'_special', $rs);
	}
	
	$GLOBALS['we']->deletecache('special');

	//refresh
	autolocate('?cid='.$this->cid);

	$sucmsg = '<li>保存成功,窗口将在二秒后自动返回专题管理!</li>'.PHP_EOL;
	$sucmsg .= '<li><a href="?cid='.$this->cid.'">返回专题管理</a></li>'.PHP_EOL;

	ajaxinfo ( $sucmsg );
} // end func


function FormContent(){
	$h = $GLOBALS['we']->style(sp, 'formcontent');
	
	$id = $GLOBALS['we']->rqid('id', -1);

	$h = str_replace('{$action}', '?act=savecontent&amp;cid={$cid}&amp;id='.$id, $h);

	$sql = 'select * from `'.sheet.'_special` where id='.$id;

	$h = $GLOBALS['we']->repm($sql, $h);

	$GLOBALS['html']->addjs(webdir.'ckeditor/ckeditor.js');
	$GLOBALS['html']->dohtmlad($h);
} // end func


function SaveContent(){
	$id = $GLOBALS['we']->rqid('id', -1);

	$rs['content'] = $GLOBALS['we']->request('描述', 'content', 'post', 'char', 1, 25500);
		
	ajaxerr();

	$GLOBALS['we']->pdo->update(sheet.'_special', $rs, ' id='.$id);

	$GLOBALS['we']->deletecache('special');

	autoclose(2000);

	$sucmsg = '<li class="h1">保存成功!</li>'.PHP_EOL;
	$sucmsg .= '<li>窗口将在二秒后自动关闭!</li>'.PHP_EOL;

	ajaxinfo ($sucmsg);
} // end func


function DoDel(){
	$id = $GLOBALS['we']->rqid('id', -1);

	$sql = 'delete from `'.sheet.'_special` where id='.$id;

	$GLOBALS['we']->execute($sql);

	htmlok();
} // end func



}

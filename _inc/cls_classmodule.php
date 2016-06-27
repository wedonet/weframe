<?php
/**
 * 模块分类管理
 *
 * @YilinSun
 * @version 1.0
 */

define('sp', 'admin/_admin_class');


class Cls_Class{

private $cid;
private $module; //模块,用于在一个频道下的多种分类方式





function Main(){
	$act = $GLOBALS['we']->ract();

	switch ($act) {
		case ''					: $this->ShowList(); break;
		case 'creat'			: $this->MyForm(FALSE); break;	
		case 'nsave'			: $this->SaveForm(FALSE); break;
		case 'del'				: $this->DoDel(); break;
		case 'edit'				: $this->MyForm(TRUE); break;
		case 'esave'			: $this->SaveForm(TRUE); break;

		case 'savecls'			: $this->SaveCls(); break;
	}
} // end func

function ShowList(){
	$h = $GLOBALS['we']->style(sp, "main");
	$tli = $GLOBALS['we']->style(sp, "li");
	
	$sql = 'select * from `'.sheet.'_class` where cid='.cid.' order by treeid asc';

	$li = $GLOBALS['we']->repm($sql, $tli);

	$h = str_replace('{$cid}', $this->cid, $h);
	$h = str_replace('{$li}', $li, $h);

	
	$GLOBALS['html']->adhead();
	$GLOBALS['html']->crumbad();

	echo $h;

	$GLOBALS['html']->adfoot();
} // end func

function MyForm($isedit){
	$h = $GLOBALS['we']->style(sp, "form");

	$id = $GLOBALS['we']->rqid('id');

	$h = str_replace('{$optionclass}', $this->GetOptionClass($this->cid, $id), $h);

	if ($isedit) {
		crumb('编辑分类');

		$id = $GLOBALS['we']->rqid('id');
		$sql = 'select * from `'.sheet.'_class` where id='.$id;
		$h = str_replace('{$action}', '?act=esave&amp;cid='.$this->cid.'&amp;id='.$id, $h);
		$h = str_replace('{$th}', '编辑分类', $h);
		
		$h = $GLOBALS['we']->repm($sql, $h);
	}
	else {
		crumb('添加分类');

		$h = str_replace('{$action}', '?act=nsave&amp;cid='.$this->cid, $h);
		$h = str_replace('{$th}', '添加分类', $h);
		$h = str_replace('{$cls}', '100', $h);
		$h = str_replace('{$mypercent}', '100', $h);
		$h = str_replace('{$isgood}', '0', $h);

		$h = $GLOBALS['we']->removemdbfield($h, sheet.'_class');
	}

	$GLOBALS['html']->adhead();
	$GLOBALS['html']->crumbad();

	echo $h;

	$GLOBALS['html']->adfoot();   
} // end func

function SaveForm($isedit){
	/*接收*/
	$title = $GLOBALS['we']->request('名称', 'title', 'post', 'char', 1, 255);
	$readme = $GLOBALS['we']->request('描述', 'readme', 'post', 'char', 1, 500, '', FALSE);
	$preimg = $GLOBALS['we']->request('预览图', 'preimg', 'post', 'char', 1, 255, 'encode', FALSE);
	$tags = $GLOBALS['we']->request('标签', 'tags', 'post', 'char', 1, 50, '', FALSE);
	$sdir = $GLOBALS['we']->request('目录', 'sdir', 'post', 'char', 1, 50, 'folder', FALSE);
	$tip = $GLOBALS['we']->request('提示', 'tip', 'post', 'char', 1, 255, 'encode', FALSE);

	$mystyle = $GLOBALS['we']->request('模板', 'mystyle', 'post', 'char', 1, 255, 'folder', FALSE);
	$mykeywords = $GLOBALS['we']->request('Keywords', 'mykeywords', 'post', 'char', 1, 255, 'encode', FALSE);
	$mydescription = $GLOBALS['we']->request('Description', 'mydescription', 'post', 'char', 1, 255, 'encode', FALSE);
	
	$isgood = $GLOBALS['we']->rfid('isgood', 0);
	$isshow = $GLOBALS['we']->rfid('isshow', 1);

	$pid = $GLOBALS['we']->rfid('pid', 0);
	$cls = $GLOBALS['we']->rfid('cls', 100);
	$mypercent = $GLOBALS['we']->rfid('mypercent', 100);

	ajaxerr();

	/*检测=====================================*/
	/*编辑时接收id, 编辑时提取原来的分类信息, 并检测PID不能是自己*/
	if ($isedit) {
	    $id = $GLOBALS['we']->rqid('id'); //本条记录id

		/*编辑时提取原来的分类信息*/
	    $sql = 'select * from `'.sheet.'_class` where id='.$id;
		$rssource = $GLOBALS['we']->exeone($sql);

		if ($pid>0) {
			if ($pid == $id) {
				ajaxerr('父分类不能是自已');
			}	
		}
	}

	/*有父分类时, 提取父分类信息*/
	if ( $pid>0 ) {
		$sql = 'select * from `'.sheet.'_class` where id='.$pid;		
		$rsparent = $GLOBALS['we']->exeone($sql);	    
	}


	/*检查同名目录*/
	/*只检测父目录下同名的,行不通, 转移分类时还要检测太乱了*/
	/*填写分类的情况下才检测, 没填时用分类ID代替目录名*/
	if ( strlen($sdir)>0 ) {
	    $sql = 'select count(*) from `'.sheet.'_class` where  sdir = "'.$sdir.'"';
		if ( $GLOBALS['we']->execount($sql) > 0 ) {
		    ajaxerr('有同名目录, 请重新填写');
		} 
	}


	/*Save Form*/
	$rs['sdir']			= $sdir;
	$rs['title']		= $title;
	$rs['readme']		= $readme;
	$rs['tags']			= $tags;
	$rs['preimg']		= $preimg;

	$rs['cls']			= $cls;
	$rs['mypercent']	= $mypercent;
	$rs['tip']			= $tip;

	$rs['mystyle']		= $mystyle;
	$rs['mykeywords']	= $mykeywords;
	$rs['mydescription']= $mydescription;
	
	
	$rs['pid']			= $pid;

	$rs['isshow']		= $isshow;
	$rs['isgood']		= $isgood;
	$rs['ispass']		= 1; //目前都是通过审核的

	if ($isedit) {
		/*父路径改变时, 更新路径信息和深度*/
		if ($rssource['pid'] != $pid) {
		    $rs['depth'] = $rsparent['depth']*1+1;
			$rs['idpath'] = $rsparent['idpath'].$id.','; 
		}

		$GLOBALS['we']->pdo->update(sheet.'_class', $rs, ' id='.$id);
	}
	else {
	    $rs['cid'] = $this->cid;
		$rs['module'] = $this->module;

		$id = $GLOBALS['we']->pdo->insert(sheet.'_class', $rs);



		//没有sdir时,用id做为sdir
		if ( $sdir == '' ) {
		   $sql = 'update `'.sheet.'_class` set sdir="'.$id.'" where id='.$id;
		   $GLOBALS['we']->execute($sql);
		}		
	}


	/*更新idpath, depth*/
	if ($isedit) {
	    if ($rssource['pid'] != $pid.'') {
			/*更新下级的*/
			$sql = 'select id,idpath from `'.sheet.'_class` where idpath like "'.$rssource['idpath'].'%"';
			$rs = $GLOBALS['we']->execute($sql)['rs'];

			$mycount = count($rs);
			if ($mycount>0) {
				/*循环原来的下级分类*/
			    for ($i=0; $i<$mycount; $i++) {
					$idpathson = $this->getidpathson($rssource['idpath'], $rsparent['idpath'].$id.',' ,$rs[$i]['idpath']);
					$sql = 'update `'.sheet.'_class` set idpath="'.$idpathson.'"';
					$sql .= ',depth='.(substr_count($idpathson, ',')-1);
					$sql .= ' where id='.$rs[$i]['id'];
					//echo $sql;
					//echo '<br />';
					$GLOBALS['we']->execute($sql);
			    }
			}
	    }
	}
	else {
		if ( $pid==0 ) {
		    $idpath = $id.',';
			$depth = 0;
		}
		else {
		    $idpath = $rsparent['idpath'].$id.',';
			$depth = $rsparent['depth']+1;
		}

		$sql = 'update `'.sheet.'_class` set idpath = "'.$idpath.'", depth='.$depth.' where id='.$id;

		$GLOBALS['we']->execute($sql);   
	}


	/*排序*/
	$this->doset(0);

	/*清除分类缓存*/
	$GLOBALS['we']->deletecacheclass($this->cid, $this->module);
	
	autolocate('?cid='.$this->cid);

	$sucmsg = '<li>保存成功,窗口将在二秒后自动返回分类管理!</li>'.PHP_EOL;
	$sucmsg .= '<li><a href="?cid='.$this->cid.'">返回分类管理</a></li>'.PHP_EOL;

	ajaxinfo( $sucmsg );   
} // end func

function DoDel(){
	$id = $GLOBALS['we']->rqid('id');

	/*提取idpath*/
	$sql = 'select idpath from `'.sheet.'_class` where id='.$id;
	$idpath = $GLOBALS['we']->exeone($sql)['idpath'];


	/*删除此分类*/
	$sql = 'delete from `'.sheet.'_class` where idpath like "'.$idpath.'%"';
	$GLOBALS['we']->execute($sql);
	htmlok();

	if (function_exists('moduledelclass')) {
	    moduledelclass($this->cid, $idpath);
	}
} // end func


/**
 * 批量保存分类cls
 */
function SaveCls(){	
	$idlist = $GLOBALS['we']->ridlist('id');
	$cls = $GLOBALS['we']->ridlist('cls');	

	if ( $idlist != '') {
		$idlist = explode(',', $idlist);
		$cls = explode(',', $cls);


		for ($i=0; $i<count($idlist); $i++) {
			$sql .= 'update `'.sheet.'_class` set cls='.$cls[$i].' where cid='.$this->cid.' and id='.$idlist[$i].';';
		}

		if (isset($sql)) {
			$GLOBALS['we']->execute($sql);
		}
		/*重新排序*/
		$this->doset(0);	    
	}


	jsucok();
} // end func

////////////////////////////////////////////////////////////////////////////

/**
 * Short 跟据频道ID,返回本频道分类Option.
 * @param   type    $cid    频道ID
 * @return  str of option
 */
function GetOptionClass($cid, $classid){
	$tli = '<option value="{$id}" class="odepth{$depth}">{$title}</option>'.PHP_EOL;

	$sql = 'select * from `'.sheet.'_class` where cid='.$cid;

	//不显示原分类的下级
	if ($classid>0) {
		$rs = $GLOBALS['we']->exeone('select idpath from `'.sheet.'_class` where id='.$classid);
	    $sql .= ' and idpath not like "'.$rs['idpath'].'"';
	}

	$sql .= ' order by treeid asc ';

	$li = $GLOBALS['we']->repm($sql, $tli); 

	return $li;
} // end func

function doset($pid){
    static $rs;
	static $rstree;
	static $treeid;
	


	if (strlen($treeid) == 0) {
	    $treeid = 1;
	}

	if ( !is_array($rs)) {
		$sql = 'select id,pid from `'.sheet.'_class` where 1 ';
		$sql .= ' and cid='.$this->cid;
		$sql .= ' order by cls asc, id asc ';	    

		$rs = $GLOBALS['we']->execute($sql)['rs'];
	}

	$mycount = count($rs);
	unset($sql);

	if ($mycount>0) {
	    for ($i=0; $i<$mycount; $i++) {
	        if ($rs[$i]['pid'] == $pid ) {
	            $sql = 'update `'.sheet.'_class` set treeid='.$treeid.' where id='.$rs[$i]['id'];
				$GLOBALS['we']->execute($sql);

				$treeid++;

				$this->doset($rs[$i]['id']);
	        }
	    }
	}
} // end func


/**
 * 生成新的idpath.
 * $sourcepidpath = 原来的父分类的idpath
 * $newidpath = 新的父分类idpath
 * $myidpath = 我原来的idpath
 */
function getidpathson( $sourcepidpath, $newpidpath, $myidpath ){
	//echo '$sourcepidpath='.$sourcepidpath;
	//echo '$newpidpath='.$newpidpath;
	//echo '$myidpath='.$myidpath;
	//echo '<br />';
    /*计算原来的父idpath长度,在我的idpath中截取掉*/
	$i = strlen($sourcepidpath);

	$s = substr($myidpath, $i);

	/*新的idpath加上截取后的idpath就是现在的idpath*/
	$s = $newpidpath.$s;;
	return $s;
} // end func




} // end class



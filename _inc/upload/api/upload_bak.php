<?php
san();


//==================================================

function Main(){
	//$fromeditor = TRUE;
	$fromeditor = $GLOBALS['we']->rqid('fromeditor');
	$preid = $GLOBALS['we']->request('preid', 'preid', 'get', 'char', 1, 50, '', FALSE);
	$obj = $GLOBALS['we']->request('obj', 'obj', 'get', 'char', 1, 50, '', false);

	$h = $GLOBALS['we']->style(sp, 'main');

	$js = '$("#ftype'.Ftype.'").addClass("on");'.PHP_EOL;

	$h = str_replace('{$webdir}', webdir, $h);
	$h = str_replace('{$ftype}', Ftype, $h);
	$h = str_replace('{$preid}', $preid, $h);
	$h = str_replace('{$obj}', $obj, $h);
	$h = str_replace('{$todeitor}', $GLOBALS['we']->request('toeditor', 'toeditor', 'get', 'char', 1, 50, 'invalid', FALSE), $h);

	$h = str_replace('{$fromeditor}', $fromeditor, $h);

	//显示在 编辑器里面时，加上头部
	if ( $fromeditor == 1 ) {

		//隐藏面包宵
		$js .= '$("#th").hide();';

		$h = str_replace('{$js}', $js, $h);

		$h = str_replace('{$funcnum}', $_GET['CKEditorFuncNum'], $h);

		$GLOBALS['html']->adhead();

	    echo $h;
		echo '</div>';
	}
	else {
		$h = str_replace('{$js}', $js, $h);

	    echo $h;
	}
} // end func



/**
 * 显示左侧分类导航.
 */
function ShowClass(){
	$h = $GLOBALS['we']->style(sp, 'divclass');
	$tli = '<li><a href="?act=list&amp;ftype='.Ftype.'&amp;classid={$id}" target="main">{$title}</a></li>';
	
	$sql = 'select * from `'.sheet.'_upclass` where ftype = '.Ftype;
	$sql .= ' and uid='.$GLOBALS['we']->u_id; 

	$li = $GLOBALS['we']->repm($sql, $tli);

	$h = str_replace('{$li}', $li, $h); 
	$h = str_replace('{$ftype}', Ftype, $h); 
	$h = str_replace('{$webdir}', webdir, $h); 

	echo  $h;    
} // end func



function HtmlList(){
	//$fromeditor = TRUE;
	$js = '';

	switch (Ftype) {
		case 1 :
			$h = $GLOBALS['we']->style(sp, 'ulpic');
			$tli = $GLOBALS['we']->style(sp, 'lipic');

			$mypagecount = 12;
			break;
		case 2 :
			$h = $GLOBALS['we']->style(sp, 'ulfile');
			$tli = $GLOBALS['we']->style(sp, 'lifile');

			$mypagecount = 10;			
			break;
		default :
			$h = $GLOBALS['we']->style(sp, 'ulfile');
			$tli = $GLOBALS['we']->style(sp, 'lifile');

			$mypagecount = 10;
			break;
	}

	//接收
	$myclassid = $GLOBALS['we']->rqid('classid');
	$classname = GetClassName($myclassid);
	$fromeditor = $GLOBALS['we']->rqid('fromeditor', 0);

	
	$utype = $GLOBALS['we']->request('utype', 'utype', 'post', 'char', 1, 50, 'invalid', FALSE);

	//处理
	if ($myclassid<0) {
		$myclassid =0;
	}

	if ($utype = '') {
		$utype = '0,1';
	}

	$tli = str_replace('{$href}', '{$urlfile}', $tli); 

	$sql = 'select * from `'.sheet.'_uplist` where isdel=0 and ftype='.Ftype.' and uid='.$GLOBALS['we']->u_id;
	
	if ( $myclassid>-1 ) {
		$sql .= ' and myclassid='.$myclassid;
	}

	//来自在线编辑器时加
	if ( $fromeditor == 1 ) {
		$h = str_replace('{$funcnum}', $_GET['funcnum'], $h);
		$js .= '$("a.url").AddUrl();';
	}
	else {
		$js .= 'formatfilelink();';
	}


	//if ( $utype != '' ) {
	//	$sql .= ' and utype in ('.$utype.')';

	//	$js .= 'checkcheckbox("utype", "'.$utype.'")'.PHP_EOL;
	//}

	$sql .= ' order by id desc';

	$li = $GLOBALS['we']->repm($sql, $tli, null, $mypagecount, True);

	$h = str_replace('{$action}', '?act=savefile&amp;myclassid='.$myclassid.'&amp;ftype='.Ftype, $h);

	//$h = str_replace('{$myclassid}', $myclassid, $h);
	$h = str_replace('{$classname}', $classname, $h);
	//$h = str_replace('{$ftype}', $ftype, $h);
	$h = str_replace('{$js}', $js, $h);
	$h = str_replace('{$li}', $li, $h);
	$h = str_replace('{$pagelist}', $GLOBALS['we']->pagelist(), $h);


	$GLOBALS['html']->adhead();
	echo $h;
	echo '</div></body></html>';
} // end func



function SaveFile(){
	$andpreimg = 1;

	$myclassid = $GLOBALS['we']->rqid('myclassid');

	if ($myclassid<0) {
		$myclassid = 0;
	}

	$upload = new CI_Upload();

	$uploadpath = getuploadpath(Ftype);

	$psyfile = $GLOBALS['we']->mappath($uploadpath);

	$config['upload_path'] = $psyfile;

	switch (Ftype) {
		case 1 :
			/*是图片*/
			$config['is_image']= TRUE;

			/*这里准备加参数设置*/
			$config['allowed_types'] = '*';
			$config['max_size']='1024';
			$config['max_width']='600';
			$config['max_height']='430';

			break;
		case 2:
			/*检测是不是flash*/
			break;
		case 3:
			/*检测是不是允许上传*/
			break;
		default:
			showerr(1022);
			break;
	}



	/*文件名 = 用户ID, 当前日期时间, 随机数*/
	$config['file_name'] = $GLOBALS['we']->user['id'].date('YmdHis').rand(1000, 9999);


	$upload->initialize($config);

	$upload->do_upload('file1');

	if (count($upload->error_msg)>0) {
	    $upload->error_msg[0];
		showerr( $upload->error_msg[0] );
	}
	else{
		$data = $upload->data();

		/*上传完了,进行处理*/
		switch (Ftype) {
			case 1 :
				/*图片*/
				if (!$data['is_image']) {
					showerr(1022);
				};

				/*宽或高大于200,生成预览图*/
				if ($upload->image_width>260 OR $upload->image_height>260) {

					/*开始生成预览图*/					
					$config['image_library'] = 'gd2';
					$config['source_image'] = $psyfile.$data['file_name'];
					$config['new_image'] = $psyfile.'thumb/';
					$config['create_thumb'] = TRUE;
					$config['thumb_marker'] = '';
					$config['maintain_ratio'] = TRUE;
					$config['width']= 260;
					$config['height'] = 260;

					$img = new CI_Image_lib($config);

					$img->resize();

					if ($img->display_errors() == '') {
						//缩略成功,生成一个预览图路径
						//$urlthumb = $thumb_path .$upload->file_name;  //多余

						$rs["urlthumb"]		= $uploadpath.'thumb/'.$data['file_name'];
					}
					else {
						echo ( $img->display_errors());

						//$urlthumb = $full_path;
						//缩略图失败,用原图做缩略图路径
					}

					unset($img);									
				}
				else{
					$rs["urlthumb"]		= $uploadpath.$data['file_name'];
				}
				
				/*向上传列表中添加图片地址及属性*/
				$sql = '';
				break;
			case 2:
				/**/
				break;
			default:
				break;
		}

		/*取得原始文件名, 不带后缀*/
		$title = $upload->client_name;
		$title = explode('.',$title)[0];

		//把路径和文件信息插入数据库
		$rs["uid"]			= $GLOBALS['we']->u_id;
		$rs["u_nick"]		= $GLOBALS['we']->u_nick;

		/*暂时用文件名做描述*/
		$rs["title"]		= $title;
		$rs["urlfile"]		= $uploadpath.$data['file_name'];
		
		$rs["ftype"]		= Ftype;

		
		$rs["stime"]		= time();
		$rs["filesize"]		= $upload->file_size;
		$rs["ufilewidth"]	= $upload->image_width;
		$rs["ufileheight"]	= $upload->image_height;
		$rs["myclassid"]	= $myclassid;


		$GLOBALS['we']->pdo->insert(sheet.'_uplist', $rs);

		htmlok();
	}

	unset ($upload);

	htmlok();	
} // end func

//==================================================================
function GetClassName($myid){
	switch ($myid) {
		case 0 :
			return '未分类的记录';
			break;
		case -1 :
			return '全部分类的记录';
			break;
		default :
			$sql = 'select title from `'.sheet.'_upclass` where id='.$myid.' and uid='.$GLOBALS['we']->u_id;
			
			$r = $GLOBALS['we']->execute($sql);

			if ($r !== FALSE) {
				return $r['title'];
			}
			else {
				return '';
			}
			break;
	}
} // end func


/*
 *生成上传路径*
 *输入 $ftype=文件类型*
 *输出字符串路径, 输出前进行检测, 不存在则创建路径*
 */
function getuploadpath($ftype){
	/*总上传路径*/
	$s = '/_upload/';

	switch ($ftype) {
		case 1:
			$s .= 'images/';
			break;
		case 2:
			$s .= 'flash/';
			break;
		case 3: 
			$s .= 'files/';	
			break;	
		default :
			showerr('文件类型错误!');
			break;
	}

	$s .= date('Ym').'/';

	$psyfile = $GLOBALS['we']->mappath($s);

	/*if 路径不存在,则新建文件夹*/
	if ( !file_exists($psyfile) ) {
		mkdir($psyfile, 0777);
	}

	/* if 预览路径不存在,则新建预览文件夹路径 */
	if ( !file_exists($psyfile.'thumb/')) {
		mkdir($psyfile.'thumb/', 0777);
	}


	return $s;
} // end func




function DoDel(){
	$id = $GLOBALS['we']->rqid('id');

	$sql = 'update `'.sheet.'_uplist` set isdel=1 where 1'; 
	$sql .= ' and id='.$id;
	$sql .= ' and isdel=0 ';
	$sql .= ' and uid='.$GLOBALS['we']->u_id;

	$GLOBALS['we']->execute( $sql );

	htmlok();
} // end func


function FormTitle(){

	$id = $GLOBALS['we']->rqid('id');

	$sql = 'select * from `'.sheet.'_uplist` where uid='. $GLOBALS['we']->user['id'];
	$sql .= " and id=".$id;

	$h = $GLOBALS['we']->style(sp, 'formtitle');

	$h = str_replace('{$action}', '?act=savetitle&amp;id='.$id, $h);

	$h = $GLOBALS['we']->repm( $sql, $h );

	echo ( $h );
} // end func



function SaveTitle(){
	$id = $GLOBALS['we']->rqid('id');

	$p['title'] = $GLOBALS['we']->request('名称', 'title', 'post', 'char', 1, 50, 'encode');
	
	ajaxerr();

	$where = ' uid='.$GLOBALS['we']->user['id'];
	$where .= ' and id='.$id;

	$GLOBALS['we']->pdo->update(sheet.'_uplist', $p, $where);

	$js = '<script type="text/javascript">'.PHP_EOL;
	$js .= '<!--'.PHP_EOL;
	$js .= '$("#title_'.$id.'").html("'.$p['title'].'");'.PHP_EOL;
	$js .= '//-->'.PHP_EOL;
	$js .= '</script>'.PHP_EOL;
	
	echo $js;
	
	jsucclose();
} // end func

/*


Sub SaveTitle()
	Dim title
	Dim sql, js

	id = WeDoNet.Rqid("id")
	title = WeDoNet.RequestStr("名称", "title", "post", 1, 1, 50, 30, 1)

	Html.AjaxErr("")

	sql = "update "& sh &"_uplist set title='"& WeDoNet.Checkstr(title) &"' where uid="& WeDoNet.user_id &" and id=" & id

	WeDoNet.Execute(sql)

	js = "<script type=""text/javascript"">" & vbcrlf
	js = js & "<!--" & vbcrlf
	js = js & "$(""#title_"& id &""").html("""& title &""");" & vbcrlf
	js = js & "//-->" & vbcrlf
	js = js & "</script>" & vbcrlf
	
	Response.Write js
	
	Html.JSucClose()
End Sub

Function GetOptionMyclass()
	Dim sql, tli, li

	tli = "<option value=""{$id}"">{$title}</option>" & vbcrlf

	sql = "select * from ["& sh &"_upclass] where 1=1 "
	sql = sql & " and uid="& WeDoNet.user_id 
	sql = sql & " order by cls asc, id asc "

	GetOptionMyclass = WeDoNet.RepM(sql, tli, "", 1, "")
End Function 
%>
*/
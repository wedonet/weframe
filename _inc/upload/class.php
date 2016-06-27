<?php


/**
 * 上传文件分类管理.
 * 
 * @author  YilinSun
 * @version 1.0
 * @package main
 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

//require_once('class.php');


class myclass extends cls_template {

   function __construct() {

      require_once('api/class_.php'); //访问接口去

		$this->ftype = $this->get('ftype');

      $this->act = $this->ract();

      switch ($this->act) {
         case '':
            $this->Main(); //主内容区
            break;
      }
   }

   function Main() {
		//$classid = $this->rid();

		$fic = $this->get('fic');
		
		function headplus(){
			echo '<base target="main" />'.PHP_EOL;
		}
		
		require_once('header.php');

      ?>	


		<div style="margin-bottom:5px;height:462px;width:140px;overflow-y:scroll;text-align:left;">
			<div style="margin-bottom:5px">
				<b>分类</b> <span style="display:none"> [<a href="?act=class&amp;ftype={$ftype}">管理分类</a>]</span>
			</div>
			<ul class="fileclass" id="fileclass">
				<?php
				if('' !== $fic){
					echo '<li><a href="upload.php?act=list&amp;ftype='.$this->ftype.'&amp;fic='.$fic.'">当前记录</a></li>';
				}
				?>

				<li><a href="upload.php?act=list&amp;ftype=<?php echo $this->ftype ?>&amp;classid=0">默认分类</a></li>

				<li><a href="upload.php?act=list&amp;ftype=<?php echo $this->ftype ?>&amp;classid=-1">所有分类的记录</a></li>
				
			</ul>
		</div>

		<script>
		<!--
			$(document).ready(function(){
				$('#fileclass li:first a').addClass('on');

				$('#fileclass a').bind('click', function(){
					$('#fileclass a.on').removeClass('on');
					$(this).addClass('on');

					
				})
			})
			
		//-->
		</script>

		</body>
		</html>

      <?php
   }



}

$myclass = new myclass();
unset($myclass);



/**
 * 上传文件的分类管理.
 * 
 * @author  CU 
 * @version 1.0
 * @package main
 */

/*
<%
'<syl>附件管理器的分类管理<by syl>

Sub ListClass()
	Dim sql, tli, li

	h = WeDoNet.Style(sp, "ulclass")
	tli = WeDoNet.Style(sp, "liclass")

	sql = "select * from ["& sh &"_upclass] where ftype="& ftype
	sql = sql & " and uid=" & WeDoNet.user_id

	li = WeDoNet.RepM(sql, tli, 9, 0, "")

	H = Replace(H, "{$ftype}", ftype)
	H = Replace(H, "{$li}", li)
	H = Replace(H, "{$pagelist}", page.pagelist("", 1))

	Html.adhead()
	Response.Write h
	Response.Write "</body></html>"
End Sub

Sub SaveClass(isedit)
	Dim title
	Dim sql

	title = WeDoNet.Requeststr("分类名称", "title", "post", 1, 1, 20, 30, 1)	

	Html.ajaxerr("")
	
	If isedit Then 
		sql = "update "& sh &"_upclass set title='"& WeDoNet.Checkstr(title) &"' where id=" & WeDoNet.Rqid("id")
		sql = sql & " and uid = "& WeDoNet.user_id
	Else 
		sql = "insert into "& sh &"_upclass (uid, ftype, title) values ("& WeDoNet.user_id &", "& ftype &", '"& WeDoNet.Checkstr(title) &"')"
	End If 

	WeDoNet.execute(sql)

	sucmsg = "<li class='h1'>保存成功</li>" & vbcrlf
	sucmsg = sucmsg & "<li>系统将自动返回</li>" & vbcrlf

	Html.Jok()
	Html.ajaxinfo sucmsg, 1

	'ajaxinfo sucmsg, 1
End Sub

Public Sub Jok()
	Response.Write "<script type=""text/javascript"">"
	Response.Write "setTimeout('ReloadFrame()', '500');"
	Response.Write "</script>"
End Sub

'<syl>删除分类<by syl>
Sub DelClass()
	Dim sql

	'<syl>删除分类<by syl>
	sql = "delete from "& sh &"_upclass where id= " & WeDoNet.Rqid("id")
	sql = sql & " and uid=" & WeDoNet.user_id

	WeDoNet.execute(sql)

	'<syl>更新原分类的文件所属分类为0(不属于任何分类)<by syl>
	sql = "update "& sh &"_uplist set myclassid=0 where id= " & WeDoNet.Rqid("id")
	sql = sql & " and uid=" & WeDoNet.user_id

	WeDoNet.execute(sql)

	Jok()
End Sub

Sub FormClass()
	Dim sql

	H = WeDoNet.Style(sp, "formclass")

	id = WeDoNet.Rqid("id")

	sql = "select * from ["& sh &"_upclass] where id=" & id
	sql = sql & " and uid=" & WeDoNet.user_id

	H = Replace(H, "{$action}", "?act=esaveclass&amp;id=" & id)
	h = WeDoNet.RepM(sql, h, "", 1, "")
	
	Response.Write h
End Sub
%>
*/
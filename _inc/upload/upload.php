<?php
/**
 * 上传文件.
 * 
 * @author  YilinSun
 * @version 1.0
 * @package main
 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');



class myclass extends cls_template {
	private $filepath;

   function __construct() {
		$this->filepath = webdir . '_inc/upload/';

      require_once('api/upload_.php'); //访问接口去
         require_once(adminpath.'checkpower.php'); //检测权限

      $this->act = $this->ract();

      switch ($this->act) {
         case '':
            $this->Main(); //主内容区
            break;

			case 'list':
				$this->htmllist();
				break;
			case 'saveimage':
				$this->saveimage();
				break;
      }
   }

   function Main() {
		//require_once('header.php');
		$ftype = $this->get('ftype');
		$obj = $this->get('obj');
		$preid = $this->get('preid');
		$fic = $this->get('fic');


		$fromeditor = $this->get('fromeditor'); //是否用在编辑器里

		if(''==$ftype){
			$ftype=1;
		}

		if(1==$fromeditor){
			require('header.php');
		}


      ?>
      <div style='width:820px' id="upload">
         <div class='th' id="th">选择文件</div>
         <div class='ac'>
            <input type="text" id="focus" class="focusbug" /> <!-- 解决ie focus bug -->
            <input type="hidden" id="obj" value="<?php echo $obj ?>" />
            <input type="hidden" id="preid" value="<?php echo $preid ?>" />
            <input type="hidden" id="fromeditor" value="<?php echo $fromeditor ?>" />
				<input type="hidden" id="ispre" value="<?php echo $this->get('ispre') ?>" />

            <div class="tabup" id="tabup" style="display:none">
               <ul>
                  <li id="ftype1"><a href="javascript:void(0)" onclick='return filefrom(1)'>图片</a></li>
                  <li id="ftype2"><a href="javascript:void(0)" onclick='return filefrom(2)'>Flash</a></li>
                  <li id="ftype3"><a href="javascript:void(0)" onclick='return filefrom(3)'>附件</a></li>
               </ul>
               <div class='clear'></div></div>

            <div class="fleft" style="width:140px;height:468px;">		
               <iframe name="frameclass" id="frameclass" src="<?php echo $this->filepath.'class.php?ftype='.$ftype.'&amp;classid=0&amp;fic='.$fic ?>" frameborder="0" scrolling="no" width="100%"  height="100%"></iframe>
            </div>
            <div id="upcontent" class="fright" style="width:660px;height:460px;">
               <!--<iframe name="main" id="main" src="<?php echo $this->filepath ?>upload.php?act=list&amp;ftype={$ftype}&amp;funcnum={$funcnum}&amp;fromeditor={$fromeditor}" frameborder="0" scrolling="yes" width="100%" height="100%"></iframe>-->
					<iframe name="main" id="main" src="<?php echo $this->filepath ?>upload.php?act=list&amp;fic=<?php echo $fic ?>" frameborder="0" scrolling="yes" width="100%" height="100%"></iframe>
            </div>
         </div>
      </div>

      <script type="text/javascript">
                     <!--
                     $(document).ready(function() {
									/*取消点击背景，弹出框消失*/
									$(document).unbind('mouseup');
                      
                     })
                     //-->
      </script>

		
      <?php
   }
	
	

	
	function htmllist(){
		$j =& $GLOBALS['j'];

		$fic = $this->get('fic');

		require_once('header.php');

		?>
		<style type='text/css'>
			.main{min-width:100%}
			.listfilter{height:auto}
		</style>
		<div class="listfilter">

		<div style="display:inline;float:left;display:none;">{$classname} &nbsp;</div>

		&nbsp;&nbsp;
		允许格式：<?php echo ALLOWED_TYPES; ?>
		<form method="post" action="?act=saveimage&amp;fic=<?php echo $fic?>" enctype="multipart/form-data" id="up" style="display:inline;">
			<input type="file" size="20" name="file1" value="浏览" style='width:180px' onchange="handleFile(this)" />
			<input type="submit" value="上传" class="submit" id="submitimage" />
		</form>
		</div>
		<p></p>
		<ul class="picture">
			<?php
				foreach ($j['list']['rs'] as $v) {
					echo '<li>'.PHP_EOL;
					echo '<div class="imgborder"><a href="'.$v['urlfile'].'" target="_blank" class="url" rel="'.$v['urlthumb'].'"><img src="'.$v['urlthumb'].'" alt="" class="fileimg" /></a></div>'.PHP_EOL;
					echo '<div class="center" style="width:100%;overflow:hidden">'.PHP_EOL;
					
					echo '	<span class="j_filesize">'.$v['ufilewidth'].'*'.$v['ufileheight'].'</span>'.PHP_EOL;
					echo '	<span id="title_{$id}">'.$v['title'].'</span>'.PHP_EOL;
					echo '</div>'.PHP_EOL;

					//echo '<div class="center">'.PHP_EOL;
					//echo '<a href="?act=edittitle&amp;id={$id}" class="j_open">编辑</a><!-- | '.PHP_EOL;
					//echo '<a href="?act=del&amp;id={$id}" class="j_dellink" title="删除{$title}">删除</a>-->'.PHP_EOL;
					//echo '</div>'.PHP_EOL;
					echo '</li>'.PHP_EOL;
				}
			?>
		</ul>
		<div class="clear"></div>
		<div class="line2"></div>
		<?php $this->pagelist($j['list']['total'], 12);	?>
		<p></p>

		<script>
                     function handleFile(f) {  
                        $("#submitimage").removeAttr('disabled');
                    var files = f.files;  
                    var result = "";  
                    for (var i = 0; i < files.length; i++) {  
                        if(files[i].size>2*1024*1024){
                            
                            alert("文件大小超过限制");
                            $("#submitimage").attr('disabled',"disabled");
                        }
//                      result += "文件名：" + files[i].name + "/r/n";  
//                      result += "文件大小：" + files[i].size + "/r/n";  
//                      result += "文件类型：" + files[i].type + "/r/n";  
//                      result += "/r/n------------------------------/r/n/r/n";  
                    }  
//                    alert(result);          
                  } 
		<!--
		$(document).ready(function(){	
			$(".fileimg").LoadImage(120,90);
			
			formatfilelink();		
		})
		</script>
		</body>
		</html>
		<?php
	}

	
	function saveimage(){
		$j = & $GLOBALS['j'];

		/*成功了刷新页面，失败提示*/
		if('y' == $j['success']){

			header('Location: '.$_SERVER['HTTP_REFERER']);

			//header('Location: '.$_SERVER['PHP_SELF'].'?act=list');
		}else{
			showerr();
		}
	}

}

$myclass = new myclass();
unset($myclass);

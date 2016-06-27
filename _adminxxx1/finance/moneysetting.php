<?php
/* 财务参数设置 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {

		require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_moneysetting.php'; //访问接口去
        require_once(adminpath.'checkpower.php'); //检测权限
        $this->addcrumb('财务管理');
        $this->addcrumb('财务参数');

        $this->act = $this->ract();

        switch ($this->act) {
            case '': 
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'creat':
                $this->fname = 'myform'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板

                break;
            case 'creatnext':
                $this->fname = 'myformnext'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
          
			case 'edit':
                $this->fname = 'formedit';
                require_once( adminpath . 'main.php' ); //主模板
                break;
			case 'editnext':
                $this->fname = 'formeditnext';
                require_once( adminpath . 'main.php' ); //主模板
                break;

			case 'nsave':
			case 'nsavenext':	
			case 'esave':
			case 'esavenext':
			case 'del':
				break;
        }
    }

    function mylist() {
		crumb($this->crumb);
        ?>
        <div class="navoperate">
            <ul>
                <li>[<a href="?act=creat&amp;pid=0">添加新分类</a>]</li>
            </ul>
        </div>

        <table class='table1 j_list' cellspacing="0" id="table1">
            <tr class="j_parent">
                <th width="40">ID</th>
                <th width="100">编码</th>
                <th width="*">名 称</th>
                <th width="40">排序</th>
				<th width="40">类型</th>
                <th width="70">对用户操作</th>
                <th width="70">对商家操作</th>
                <th width="70">对平台操作</th>
               
                <th width="180">操作</th>
            </tr>

			<?php
			foreach ($GLOBALS['j']['list'] as $v ) {

				if(0 == $v['pid']){
					echo '<tr  class="j_parent">'.PHP_EOL;
					echo '<td>'.$v['id'].'</td>'.PHP_EOL;
					echo '<td>'.$v['ic'].'</td>'.PHP_EOL;
					echo '<td>'.$v['title'].'</td>'.PHP_EOL;
					echo '<td>'.$v['cls'].'</td>'.PHP_EOL;
					echo '<td></td>'.PHP_EOL;

					echo '<td>'.$v['opuser'].'</td>'.PHP_EOL;
					echo '<td>'.$v['opbiz'].'</td>'.PHP_EOL;
					echo '<td>'.$v['opplat'].'</td>'.PHP_EOL;

					echo '<td>'.PHP_EOL;

					
					echo '<a href="?act=edit&amp;id='.$v['id'].'">修改</a> &nbsp; '.PHP_EOL;
					echo '<a href="?act=del&amp;id='.$v['id'].'" title="删除'.$v['title'].'"  class="j_confirmdel">删除</a> &nbsp; '.PHP_EOL;
                                        echo '<a href="?act=creatnext&amp;pid='.$v['id'].'">添加分类</a> &nbsp; '.PHP_EOL;
					echo '</td>'.PHP_EOL;

					echo '</tr>'.PHP_EOL;		
					
					foreach ($GLOBALS['j']['list'] as $x ) {
						if($v['id'] == $x['pid']){
							echo '<tr class="j_parent">'.PHP_EOL;
							echo '<td>'.$x['id'].'</td>'.PHP_EOL;
							echo '<td>'.$x['ic'].'</td>'.PHP_EOL;
							echo '<td> &nbsp; &nbsp; &nbsp; &nbsp; '.$x['title'].'</td>'.PHP_EOL;
							echo '<td>'.$x['cls'].'</td>'.PHP_EOL;
							echo '<td>'.$x['mytype'].'</td>'.PHP_EOL;

							echo '<td>'.$x['opuser'].'</td>'.PHP_EOL;
							echo '<td>'.$x['opbiz'].'</td>'.PHP_EOL;
							echo '<td>'.$x['opplat'].'</td>'.PHP_EOL;

							echo '<td>'.PHP_EOL;

							
							echo '<a href="?act=editnext&amp;id='.$x['id'].'">修改</a> &nbsp; '.PHP_EOL;
							echo '<a href="?act=del&amp;id='.$x['id'].'" title="删除'.$x['title'].'"  class="j_confirmdel">删除</a> &nbsp; '.PHP_EOL;
							echo '</td>'.PHP_EOL;

							echo '</tr>'.PHP_EOL;

						}
					}
				}
			}

			?>

        </table> 
        <?php
    }

	function myform(){
		$j =& $GLOBALS['j'];

		$this->addcrumb('添加新分类');

		crumb($this->crumb);

		?>
		<form method="post" action="?" id="myform">
			<input type="hidden" name="act" value="nsave" />	
			<table class="table1" cellspacing="0" >

				<tr>
					<td>名称</td>
					<td><input type="text" name="title" value="" size="20" /><b class="star">&nbsp;*</b></td>
				</tr>		

				<tr>
					<td>编码</td>
					<td><input type="text" name="ic" value="" size="20" /><b class="star">&nbsp;*</b> </td>
				</tr>

				<tr>
					<td>排序</td>
					<td><input type="text" name="cls" value="100" size="3" /> </td>
				</tr>	


				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value=" 提 交 "  /></td>
				</tr>
			</table>		
		</form>

		<script type="text/javascript">

            $(document).ready(function() { 

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/
                        
        
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';
                           

                            /*弹出对话框*/
                            opdialog(mess);
                        }
                        else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>

		<?php
	}

	/**/
	function myformnext(){
		$j =& $GLOBALS['j'];

		$pid = $this->rqid('pid');

		$this->addcrumb('添加财务类型');

		crumb($this->crumb);

		?>
		<form method="post" action="?" id="myform">
			<input type="hidden" name="act" value="nsavenext" />	
			<input type="hidden" name="pid" value="<?php echo $pid ?>" />	
			<table class="table1" cellspacing="0" >

				<tr>
					<td width="120">名称</td>
					<td><input type="text" name="title" value="" size="20" /><b class="star">&nbsp;*</b></td>
				</tr>		

				<tr>
					<td>编码</td>
					<td><input type="text" name="ic" value="" size="20" /><b class="star">&nbsp;*</b></td>
				</tr>

				<tr>
					<td>排序</td>
					<td><input type="text" name="cls" value="100" size="3" /> </td>
				</tr>	

				<tr>
					<td>款项类型</td>
					<td><input type="text" name="mytype" value="" size="10" /> </td>
				</tr>	

				<tr>
					<td>对个人操作</td>
					<td><input type="text" name="opuser" value="" size="10" /><b class="star">&nbsp;*</b></td>
				</tr>


				<tr>
					<td>对商家操作</td>
					<td><input type="text" name="opbiz" value="" size="10" /><b class="star">&nbsp;*</b></td>
				</tr>

				<tr>
					<td>对平台操作</td>
					<td><input type="text" name="opplat" value="" size="10" /><b class="star">&nbsp;*</b></td>
				</tr>


				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value=" 提 交 "  /></td>
				</tr>
			</table>		
		</form>

		<script type="text/javascript">

            $(document).ready(function() { 

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/
                        
        
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';
                           

                            /*弹出对话框*/
                            opdialog(mess);
                        }
                        else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>

		<?php
	}

	
	function formedit()	{
		$j =& $GLOBALS['j'];

		$id = $this->rqid();

		$this->addcrumb('修改分类');

		crumb($this->crumb);

		$data = $j['data'];

		?>
		<form method="post" action="?" id="myform">
			<input type="hidden" name="act" value="esave" />	
			<input type="hidden" name="id" value="<?php echo $id ?>" />	
			<table class="table1" cellspacing="0" >

				<tr>
					<td>名称</td>
					<td><input type="text" name="title" value="<?php echo $data['title'] ?>" size="20" /><b class="star">&nbsp;*</b>  </td>
				</tr>		

				<tr>
					<td>编码</td>
					<td><input type="text" name="ic" value="<?php echo $data['ic'] ?>" size="20" /><b class="star">&nbsp;*</b> </td>
				</tr>

				<tr>
					<td>排序</td>
					<td><input type="text" name="cls" value="<?php echo $data['cls'] ?>" size="3" /> </td>
				</tr>	


				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value=" 提 交 "  /></td>
				</tr>
			</table>		
		</form>

		<script type="text/javascript">

            $(document).ready(function() { 

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/
                        
        
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';
                           

                            /*弹出对话框*/
                            opdialog(mess);
                        }
                        else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>

		<?php	
	}


	/**/
	function formeditnext(){
		$j =& $GLOBALS['j'];

		$id = $this->rqid();

		$this->addcrumb('修改');

		crumb($this->crumb);

		$data = $j['data'];

		?>
		<form method="post" action="?" id="myform">
			<input type="hidden" name="act" value="esavenext" />	
			<input type="hidden" name="id" value="<?php echo $id ?>" />	
			<table class="table1" cellspacing="0" >

				<tr>
					<td width="120">名称</td>
					<td><input type="text" name="title" value="<?php echo $data['title'] ?>" size="20" /><b class="star">&nbsp;*</b>  </td>
				</tr>		

				<tr>
					<td>编码</td>
					<td><input type="text" name="ic" value="<?php echo $data['ic'] ?>" size="20" /><b class="star">&nbsp;*</b> </td>
				</tr>

				<tr>
					<td>排序</td>
					<td><input type="text" name="cls" value="<?php echo $data['cls'] ?>" size="3" /> </td>
				</tr>	

				<tr>
					<td>款项类型</td>
					<td><input type="text" name="mytype" value="<?php echo $data['mytype'] ?>" size="10" /> </td>
				</tr>

				<tr>
					<td>对个人操作</td>
					<td><input type="text" name="opuser" value="<?php echo $data['opuser'] ?>" size="10" /><b class="star">&nbsp;*</b> </td>
				</tr>


				<tr>
					<td>对商家操作</td>
					<td><input type="text" name="opbiz" value="<?php echo $data['opbiz'] ?>" size="10" /><b class="star">&nbsp;*</b> </td>
				</tr>

				<tr>
					<td>对平台操作</td>
					<td><input type="text" name="opplat" value="<?php echo $data['opplat'] ?>" size="10" /><b class="star">&nbsp;*</b> </td>
				</tr>


				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value=" 提 交 "  /></td>
				</tr>
			</table>		
		</form>

		<script type="text/javascript">

            $(document).ready(function() { 

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/
                        
        
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';
                           

                            /*弹出对话框*/
                            opdialog(mess);
                        }
                        else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>

		<?php
	}
}
    

$tp = new myclass();
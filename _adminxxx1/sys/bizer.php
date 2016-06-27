<?php 
/*商家管理*/

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_inc/cls_template.php');
require_once(adminpath . 'public.php');



class myclass extends cls_template {
   function __construct() {
        $this->addcrumb('商家');

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
            case 'edit':
                $this->fname = 'formedit';
                require_once( adminpath . 'main.php' ); //主模板
                break;
            

            /* 下面几个不需要渲染 */
            case 'nsave':
                require_once AdminApiPath . 'sys'.DIRECTORY_SEPARATOR.'user.php'; //访问接口去
                break;
            case 'esave':
                require_once AdminApiPath . 'sys'.DIRECTORY_SEPARATOR.'user.php'; //访问接口去
                break;     
            case 'savepass':
                require_once AdminApiPath . 'sys'.DIRECTORY_SEPARATOR.'user.php'; //访问接口去
                break;    				
            case 'del':
                require_once AdminApiPath . 'sys'.DIRECTORY_SEPARATOR.'user.php'; //访问接口去
                break;
        }
    }

	/*商家列表*/
	function mylist()
	{
		/* 访问用户接口 */
		//$_GET['ic'] = 'bizer'; //通知接口取商家信息
		require_once(AdminApiPath . 'sys/user.php');

		$j =& $GLOBALS['j'];

		$list =& $j['userlist']['rs'];

		

		crumb($this->crumb);
		?>


        <div class="navoperate">
            <ul>
                <li><a href="?act=creat&amp;ic=bizer" class="j_open">添加商家</a></li>
            </ul>
        </div>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="30">ID</th>
                <th width="*">用户名</th>
                <th width="40">用户组</th>
                

                <th width="40">注册时间</th>
                <th width="30">审核</th>
                <th width="30">锁定</th>
                <th width="100">操作</th>
            </tr>

            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['u_name'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['u_gname'] . '</td>' . PHP_EOL;   
				
                echo '<td>' . $v['regtime'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['ischecked'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['islock'] . '</td>' . PHP_EOL;
                echo '<td><a href="?act=edit&amp;id='.$v['id'].'">编辑</a> | <a href="?act=admin&amp;id='.$v['id'].'" title="删除'.$v['id'].'" class="j_confirmdel">删除</a></td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>

        </table>
        <?php

		$this->pagelist($j['userlist']['total']);
    }


	/*添加商家*/
	function myform(){
		require_once(AdminApiPath . 'sys/user.php');
		$j =& $GLOBALS['j'];

		crumb($this->crumb);
		?>
		<form method="post" action="?" id="myform">
			<input type="hidden" name="act" value="nsave" />
			<input type="hidden" name="u_gic" value="bizer" />
			<table class="table1" cellspacing="0" >

				<tr>
					<td>用户名</td>
					<td><input type="text" name="u_name" id="u_name" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b> (6至20位数字或字母组合)</td>
				</tr>

				<tr>
					<td>联系电话</td>
					<td><input type="text" name="u_phone" id="u_phone" value="" size="20" /></td>
				</tr>

				<tr>
					<td>手机</td>
					<td><input type="text" name="u_mobile" id="u_mobile" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>
				</tr>

				<tr>
					<td>电子邮箱</td>
					<td><input type="text" name="u_mail" id="u_mail" value="" size="20" /></td>
				</tr>

	
				<tr>
					<td>密码</td>
					<td><input type="password" name="u_pass" id="u_pass" value="" size="20" /><b class="star">&nbsp;*&nbsp;</b>&nbsp;<span id="divpass" >(6至20位数字与字母两种组合)</span></td>
				</tr>

				<tr>
					<td>确认密码</td>
					<td><input type="password" name="u_pass2" id="u_pass2" value="" size="20" /><b class="star">&nbsp;*&nbsp;</b></td>
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
                        
        
                        /*保存成功，跳转到支付页*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?ic=bizer'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?ic=bizer">二秒后自动返回列表.</a></li>';
                            mess['content'] += '<li><a href="javascript:resetform()">继续添加</a></li>';

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


	
	function formedit(){
		require_once(AdminApiPath . 'sys/user.php');

		$j =& $GLOBALS['j'];

		$a_user = $j['user'];

		crumb($this->crumb);
		?>
		<form method="post" action="?" id="myform">
			<input type="hidden" name="act" value="esave" />
			<input type="hidden" name="u_gic" value="bizer" />
			<input type="hidden" name="id" value="<?php echo $a_user['id'] ?>" />
			<table class="table1" cellspacing="0" >

				<tr>
					<td width="15%">用户名</td>
					<td><?php echo $a_user['u_name'] ?></td>
				</tr>

				<tr>
					<td>联系电话</td>
					<td><input type="text" name="u_phone" id="u_phone" value="<?php echo $a_user['u_phone'] ?>" size="20" /></td>
				</tr>

				<tr>
					<td>手机</td>
					<td><input type="text" name="u_mobile" id="u_mobile" value="<?php echo $a_user['u_mobile'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>
				</tr>

				<tr>
					<td>电子邮箱</td>
					<td><input type="text" name="u_mail" id="u_mail" value="<?php echo $a_user['u_mail'] ?>" size="20" /></td>
				</tr>

	
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value=" 提 交 "  /></td>
				</tr>
			</table>		
		</form>

		<p></p><p></p>
		<form  method="post" action="?" id="formpass">
			<input type="hidden" name="act" value="savepass" />
			<input type="hidden" name="id" value="<?php echo $a_user['id'] ?>" />
			<table class="table1" cellspacing="0" >
				<tr>
					<td width="15%">密码</td>
					<td><input type="password" name="u_pass" id="u_pass" value="" size="20" />&nbsp;<span id="divpass" >(6至20位数字与字母两种组合)</span></td>
				</tr>

				<tr>
					<td>确认密码</td>
					<td><input type="password" name="u_pass2" id="u_pass2" value="" size="20" /></td>
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
                        
        
                        /*保存成功，跳转到支付页*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?ic=bizer'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?ic=bizer">二秒后自动返回列表.</a></li>';
                            

                            /*弹出对话框*/
                            opdialog(mess);
                        }
                        else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })


                $('#formpass').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/
                        
        
                        /*保存成功，跳转到支付页*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?ic=bizer'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?ic=bizer">二秒后自动返回列表.</a></li>';
                            

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
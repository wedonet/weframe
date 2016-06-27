<?php
/* data管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        require_once(AdminApiPath . 'company/_device.php');		/* 提取数据 */

		require_once( adminpath . 'checkpower.php'); //检测权限


        $this->comid = $this->rid('comid');

		$j =& $GLOBALS['j'];

		$this->addcrumb('<a href="goods.php?comid='.$this->comid.'">'.$j['company']['title'].'</a>'); //crumb加上公司名
		
		$this->addcrumb('设备');


        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;

            case 'edit':
                $this->fname = 'formedit';
                require_once(adminpath . 'main.php');
                break;
            case 'creat':
                $this->fname = 'myform';
                require_once(adminpath . 'main.php');
                break;
			case 'selplace': //设置铺位
                $this->fname = 'selplace';
                require_once(adminpath . 'main.php');		
				break;
			case 'copy': //设置铺位
                $this->fname = 'formcopy';
                require_once(adminpath . 'main.php');		
				break;


            case 'esave':
            case 'nsave':
			case 'setplace':
			case 'isrun':
			case 'unrun':
			case 'past':
                break;
           
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];    

        $list = & $j['list'];

        $comid = $this->comid;

        crumb($this->crumb);

		require_once('biztab.php'); /*商家业务选项卡*/
        
        ?>
		<p></p>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="40">ID</th>
                <th width="40">IC</th>
                <th width="*">机型</th>
                <th width="*">门数</th>
                <th width="*">是否运行</th>
                <th width="">铺位</th>
       
                <th width="200">操作</th>
            </tr>

        <?php
        foreach ($list as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['typeic'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['doornum'] . '</td>' . PHP_EOL;
            echo '<td>' . $this->yesorno($v['isrun']) . '</td>' . PHP_EOL;

            echo '<td>' . $v['building'] . '栋-' . $v['floor']. '层-' . $v['placetitle'] . '</td>' . PHP_EOL;

            echo '<td>' . PHP_EOL;
			echo '  <a href="?act=selplace&amp;deviceid=' . $v['id'] .'&amp;comid=' . $comid . '">铺位</a> &nbsp; ' . PHP_EOL;			
            echo '  <a href="doorgoods.php?deviceid=' . $v['id'] .'&amp;comid=' . $comid . '">柜门</a> &nbsp; ' . PHP_EOL;

			if( 0 == $v['isrun']){
			echo '  <a href="?act=isrun&amp;id=' . $v['id'] .'&amp;comid=' . $comid . '" class="j_confirmedit" title="运行'.$v['ic'].'">运行</a> &nbsp; ' . PHP_EOL;
			}else{
			echo '  <a href="?act=unrun&amp;id=' . $v['id'] .'&amp;comid=' . $comid . '" class="j_confirmedit" title="关闭'.$v['ic'].'">关闭</a> &nbsp; ' . PHP_EOL;
            }

			echo '  <a href="?act=copy&amp;deviceid=' . $v['id'] .'&amp;comid=' . $comid . '">复制商品</a> &nbsp; ' . PHP_EOL;	
			
            echo '</td>' . PHP_EOL; 
            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>
		
		<script type="text/javascript">
			$(document).ready(function() {
				$('a.j_confirmedit').j_confirmedit(function(json){
					/*设置成功刷新页面*/
					if('y'==json.success){
						document.location.reload();
					}
				})
			})
		</script>

        <?php
    }
    
    
    /*添加*/
      function myform() {
        $j = & $GLOBALS['j'];    
       
		$this->addcrumb('添加设备');

		$comid = $this->get('comid');	

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="nsave" />  
            <input type="hidden" name="comid" value="<?php echo $comid ?>" /> 

            
            <table class="tableform" cellspacing="1" >

                <tr>
                    <td width="60">机型</td>
                    <td width="*"><input type="text" name="typeic" id="typeic" value="" size="20"></td>
                </tr>     

               
                <tr>
                    <td>设备IC</td>
                    <td><input type="text" name="ic" id="ic" size="20" value=""></td>
                </tr>

  
                
                
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="保存" class="submit1"></td>
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
                            
                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动返回设备列表</a></li>';
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
   
	/*编辑*/
    function formedit() {
        $j = & $GLOBALS['j'];           
	
	   $this->addcrumb('编辑');
            $comid = $this->get('comid');
	   $data = $j['device'];
        
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="esave" />      
            <input type="hidden" name="comid" value="<?php echo $comid ?>" /> 
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
           
            
            <table class="tableform" cellspacing="1" >
               
                <tr>
                    <td width="60">ID</td>
                    <td width="*"><input type="text" name="id" id="id" value="<?php echo $this->ivalue($data, 'id') ?>" size="20"></td>
                </tr>

                <tr>
                    <td width="60">机型</td>
                    <td width="*"><input type="text" name="typeic" id="typeic" value="<?php echo $this->ivalue($data, 'typeic') ?>" size="20"></td>
                </tr>     

                <tr>
                    <td width="60">门数</td>
                    <td width="*"><input type="text" name="doornum" id="doornum" value="<?php echo $this->ivalue($data, 'doornum') ?>" size="20"></td>
                </tr>      

                <tr>
                    <td>铺位IC</td>
                    <td><input type="text" name="placeic" size="3" value="<?php echo $this->ivalue($data, 'placeic') ?>"></td>
                </tr>
                
                <tr>
                    <td>铺位ID</td>
                    <td><input type="text" name="placeid" size="3" value="<?php echo $this->ivalue($data, 'placeid') ?>"></td>
                </tr>
                
                <tr>
                    <td>是否运行</td>
                    <td>
                        <select name="isrun" id="isrun">
                            <option value="1" selected="selected">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                </tr>
                
                

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="保存" class="submit1"></td>
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

                            ttt = setTimeout("window.location.href='?comid=<?php echo $this->comid ?>'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动返回设备列表.</a></li>';  

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

	/*设置设备的铺位*/
	function selplace(){

		$j = & $GLOBALS['j'];    
       
		$this->addcrumb('铺位');

		$comid = $this->get('comid');	

        crumb($this->crumb);

		$data =& $j['device'];

        ?>
         
		<table class="tableform" cellspacing="1" >

			<tr>
				<td width="60">机型</td>
				<td><?php echo $data['typeic'] ?></td>
			</tr>     

			<tr>
				<td>门数</td>
				<td><?php echo $data['doornum'] ?></td>
			</tr> 
		   
			<tr>
				<td>设备IC</td>
				<td><?php echo $data['ic'] ?></td>
			</tr>

			<tr>
				<td>当前铺位</td>
				<td>
					<?php 
						if( array_key_exists('place', $j)){
							$place =& $j['place'];

							if( '' != $place['building'] ){
                                                           echo $place['building'] . '栋-' . $place['floor']. '层-' . $place['title'];
							}
						}
					?>
				</td>
			</tr>
	   </table>     

       <form method="post" action="?" id="myform">            
           <input type="hidden" name="act" value="setplace" />  
           <input type="hidden" name="comid" value="<?php echo $comid ?>" />   
		   <input type="hidden" name="deviceid" value="<?php echo $data['id'] ?>" />   
		   
		   <table class="tableform" cellspacing="1" >
				<tr>
					<td width="60">铺位ID</td>
					<td><input type="text" name="placeid" value="<?php echo $data['placeid'] ?>" size="20" /></td>
				</tr>
				<tr>
					<td>铺位IC</td>
					<td><input type="text" name="placeic" value="<?php echo $data['placeic'] ?>" size="20" /></td>
				</tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="保存" class="submit1"></td>
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

                            ttt = setTimeout("window.location.href='?comid=<?php echo $comid ?>'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动返回设备列表</a></li>';
                  

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

	/*复制售卖品*/
	function formcopy(){	

		$j = & $GLOBALS['j'];    
       
		$this->addcrumb('复制售卖品');

		$comid = $this->get('comid');	

        crumb($this->crumb);

		$data =& $j['device'];

        ?>
            
		<table class="tableform" cellspacing="1" >

			<tr>
				<td width="60">机型</td>
				<td><?php echo $data['typeic'] ?></td>
			</tr>     

			<tr>
				<td>门数</td>
				<td><?php echo $data['doornum'] ?></td>
			</tr> 
		   
			<tr>
				<td>设备IC</td>
				<td><?php echo $data['ic'] ?></td>
			</tr>

			<tr>
				<td>当前铺位</td>
				<td>
					<?php 
						if( array_key_exists('place', $j)){
							$place =& $j['place'];

							if( '' != $place['building'] ){
                                                            echo $place['building'] . '栋-' . $place['floor']. '层-' . $place['title'];
							}
						}
					?>
				</td>
			</tr>
	   </table>     

		<p></p>

       <form method="post" action="?" id="myform">            
           <input type="hidden" name="act" value="past" />  
           <input type="hidden" name="comid" value="<?php echo $comid ?>" />   
		   <input type="hidden" name="sourcedeviceid" value="<?php echo $data['id'] ?>" />   
		   
		   <table class="table1" cellspacing="1" >
				<tr><th colspan="2">复制到</th></tr>
				<tr>
					<td width="60">设备ID</td>
					<td><input type="text" name="deviceid" value="" size="20" /></td>
				</tr>
				<tr>
					<td>设备IC</td>
					<td><input type="text" name="deviceic" value="" size="20" /></td>
				</tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="复制" class="submit1"></td>
                </tr>
            </table>
                   <div class="tip1">
			<dl>
				<dt>说明</dt>
				<dd>请填写本店铺的设备ID和IC，可将原设备商品复制到新设备中！</dd>
			</dl>
		</div>
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

                            ttt = setTimeout("window.location.href='?comid=<?php echo $comid ?>'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动返回设备列表</a></li>';
                  

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

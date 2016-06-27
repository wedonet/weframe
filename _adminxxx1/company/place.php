<?php
/* 铺位管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {		
        require_once(AdminApiPath . 'company/_place.php');/* 提取数据 */

		require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->comid = $this->rid('comid');

		$j =& $GLOBALS['j'];

		$this->addcrumb('<a href="goods.php?comid='.$this->comid.'">'.$j['company']['title'].'</a>'); //crumb加上公司名
		
		$this->addcrumb('铺位');


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
            case 'esave':
            case 'nsave':
			case 'del':
                break;
           
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

 

        $list = & $j['list'];

        $comid = $this->get('comid');

        crumb($this->crumb);
require_once('biztab.php'); /*商家业务选项卡*/
        
        ?>
    <div class="navoperate">
		<ul>
			<li><a id="addself" href="?act=creat&amp;comid=<?php echo $comid ?>">添加铺位</a> &nbsp; </li>
		</ul>
		</div>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="40">位置ID</th>
                <th width="40">位置IC</th>
                <th width="*">名称</th>
                <th width="*">栋</th>
                <th width="*">楼层</th>
                <th width="*">排序</th>
                <th width="100">操作</th>
            </tr>

        <?php
        foreach ($list as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['building'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['floor'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['cls'] . '</td>' . PHP_EOL;
            echo '<td>' . PHP_EOL;
            echo '  <a href="?act=edit&amp;comid='.$comid.'&amp;id=' . $v['id'] .'">编辑</a> &nbsp; ' . PHP_EOL;
           
            echo '  <a href="?act=del&amp;comid='.$comid.'&amp;id=' . $v['id'] . '" title="删除' . $v['title'] . '" class="j_del">删除</a></td>' . PHP_EOL;
            
            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>


        <?php
    }
    
    
    /*添加*/
      function myform() {
        $j = & $GLOBALS['j'];    
       
		$this->addcrumb('添加铺位');
		$comid = $this->get('comid');
		/* 添加时排序号是100 */
		$data['cls'] = '100';

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="nsave" />  
            <input type="hidden" name="comid" value="<?php echo $comid ?>" /> 

            
            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="60">铺位IC</td>
                    <td width="*"><input type="text" name="ic" id="ic" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>栋</td>
                    <td><input type="text" name="building" id="building" size="3" value=""><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>层</td>
                    <td><input type="text" name="floor" id="floor" size="3" value=""><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                
                <tr>
                    <td width="60">名称</td>
                    <td width="*"><input type="text" name="title" id="title" value=""  maxlength="8"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>  
                
                <tr>
                    <td>排序</td>
                    <td><input type="text" name="cls"  size="3" value="100"></td>
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

                            ttt = setTimeout("window.location.href='?comid=<?php echo $this->comid ?>'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动返回铺位列表.</a></li>';
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

		/*从接口获取数据*/
	   require_once(AdminApiPath . 'company/_place.php');
		
	   $this->addcrumb('编辑');
            $comid = $this->get('comid');
	   $data = $j['data'];
        
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="esave" />      
            <input type="hidden" name="comid" value="<?php echo $comid ?>" /> 
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
           
            
            <table class="tableform" cellspacing="1" >
                
                <tr>
                    <td width="60">IC</td>
                    <td width="*"><input type="text" name="ic" id="ic" value="<?php echo $data['ic'] ?>" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>栋</td>
                    <td><input type="text" name="building" id="building" size="3" value="<?php echo $data['building'] ?>"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td>层</td>
                    <td><input type="text" name="floor" id="floor" size="3" value="<?php echo $data['floor'] ?>"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="60">名称</td>
                    <td width="*"><input type="text" name="title" id="title" value="<?php echo $data['title'] ?>"  maxlength="8"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr> 
                <tr>
                    <td>排序</td>
                    <td><input type="text" name="cls" id="cls" size="3" value="<?php echo $data['cls'] ?>"></td>
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
                            
                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动返回铺位列表.</a></li>';  

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
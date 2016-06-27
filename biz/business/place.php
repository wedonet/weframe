<?php
/* 商品管理 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');

/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        require_once(ApiPath . '/biz/business/_place.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb($j['company']['title']); //crumb加上公司名

        $this->addcrumb('铺位管理');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
			case 'edit':
			
                $this->fname = 'formedit';
                require_once(syspath . '/biz/main.php');
                break;
        }
    }

    /* 商家列表 */

    function mylist() {
        $j = & $GLOBALS['j'];
//        $place = $j['place'];
        $list = $j['list'];
        $comid = $this->get('comid'); //接收上一页传的comid
        $placeid = $this->get('placeid');

        crumb($this->crumb);
        ?>
        

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">位置ID</th>
                <th width="80">位置IC</th>
<!--                <th width="*">名称</th>
                <th width="*">栋</th>
                <th width="*">楼层</th>-->
                 <th width="*">铺位</th>
                <th width="*">排序</th>
                <th width="100">操作</th>
            </tr>

            <?php
            if (false != $list) {
                foreach ($list['rs'] as $v) {

                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
//                    echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
//                    echo '<td>' . $v['building'] . '</td>' . PHP_EOL;
//                    echo '<td>' . $v['floor'] . '</td>' . PHP_EOL;
                       echo '<td>' . $v['building'] . '栋-' . $v['floor'] . '层-' . $v['title'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['cls'] . '</td>' . PHP_EOL;
                    echo '<td><a href="?act=edit&amp;comid='.$comid.'&amp;id=' . $v['id'] .'">编辑</a></td>' . PHP_EOL;

                    echo '</tr>' . PHP_EOL;
                }
            }
            ?>
        </table>
        <?php
           $this->pagelist($list['total']);
        if ('y' != $j['success']) {
            $this->showerrlist($j['errmsg']); //把错误信息打印出来
        }
    }
	/*编辑*/
    function formedit() {
        $j = & $GLOBALS['j'];           

		/*从接口获取数据*/
	   require_once(ApiPath . '/biz/business/_place.php');
		
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
                    <td width="*"><?php echo $data['ic'] ?></td>
                </tr>

                <tr>
                    <td>栋</td>
                    <td><?php echo $data['building'] ?></td>
                </tr>
                <tr>
                    <td>层</td>
                    <td><?php echo $data['floor'] ?></td>
                </tr>
                <tr>
                    <td width="60">名称</td>
                    <td width="*"><?php echo $data['title'] ?></td>
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

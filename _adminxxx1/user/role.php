<?php
/* 角色管理 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

/* 请求数据 */

class myclass extends cls_template {

    function __construct() {

        require_once AdminApiPath . 'user' . DIRECTORY_SEPARATOR . '_role.php'; //访问接口去
        require_once( adminpath . 'checkpower.php'); //检测权限
        $this->addcrumb('用户管理');
        $this->addcrumb('用户组');
        $this->addcrumb('角色');
        
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
            case 'accessedit':
                $this->fname = 'accessedit';
                require_once( adminpath . 'main.php' ); //主模板
                break;


            /* 下面几个不需要渲染 */
            case 'nsave':
            case 'esave':
            case 'del':
            case 'accessesave':
                break;
        }
    }

    function mylist() {

        $data = & $GLOBALS['j'];

        //$this->crumb('角色');

        $pid = $this->get('pid'); //取角色pid
        crumb($this->crumb);
        ?>


        <div class="navoperate">
            <ul>
                <li><a href="?act=creat&amp;pid=<?php echo $pid ?>">添加新角色</a></li>
            </ul>
        </div>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="30">ID</th>
                <th width="*">名称</th>
                <th width="40">识别码</th>


                <th width="40">人数</th>
                <th width="40">排序</th>
                <th width="40">使用</th>
                <th width="160">操作</th>
            </tr>

            <?php
            foreach ($data['rolelist'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['countuser'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['cls'] . '</td>' . PHP_EOL;
                echo '<td>' . $this->yesorno($v['isuse']) . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                echo '	<a href="?act=edit&amp;pid=' . $pid . '&amp;id=' . $v['id'] . '">编辑</a> &nbsp; ';
                echo '	<a href="?act=accessedit&amp;pid=' . $pid . '&amp;id=' . $v['id'] . '">权限编辑</a> &nbsp; ';

                echo '	<a href="?act=del&amp;pid=' . $pid . '&amp;id=' . $v['id'] . '" title="删除' . $v['title'] . '" class="j_del">删除</a>' . PHP_EOL;
                echo '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>

        </table>

        <!--  j_del的处理，main.js函数已写，此处注释
        <script type="text/javascript">
                                $(document).ready(function () {
                        $('.j_del').j_del(function (json) {

                        })
                    })
                    //
                </script>-->
        <?php
    }

    /* 用户组表单 */

    function myform() {
        $j = & $GLOBALS['j'];

        $this->addcrumb('添加');

        $pid = $this->get('pid'); //角色pid

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="nsave" />

            <input type="hidden" name="pid" value="<?php echo $pid ?>" />

            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="60">名称</td>
                    <td width="*"><input type="text" name="title" id="title" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="60">识别码</td>
                    <td width="*"><input type="text" name="ic" id="ic" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>     

                <tr>
                    <td>排序(数字)</td>
                    <td><input type="text" name="cls" id="cls" size="3" value="100"></td>
                </tr>

                <tr>
                    <td>是否使用</td>
                    <td>
                        <select name="isuse" id="isuse">
                            <option value="1" selected="selected">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value="保存" class="submit1"></td>
                </tr>
            </table>
        </form>


        <script type="text/javascript">

            $(document).ready(function () {

                $('#myform').bind('submit', function () {
                    j_post($(this), function (json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?pid=<?php echo $pid ?>'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?pid=<?php echo $pid ?>">二秒后自动返回角色列表.</a></li>';
                            mess['content'] += '<li><a href="javascript:resetform()">继续添加</a></li>';

                            /*弹出对话框*/
                            opdialog(mess);
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    /* 编辑角色 */

    function formedit() {
        $j = & $GLOBALS['j'];

        $this->addcrumb('编辑');

        $data = $j['role'];

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="formrole">            
            <input type="hidden" name="act" value="esave" />       
            <input type="hidden" name="pid" value="<?php echo $data['pid'] ?>" />
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />


            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="60">名称</td>
                    <td width="*"><input type="text" name="title" id="title" value="<?php echo $data['title'] ?>" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="60">识别码</td>
                    <td width="*"><input type="text" name="ic" id="ic" value="<?php echo $data['ic'] ?>" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>     

                <tr>
                    <td>排序(数字)</td>
                    <td><input type="text" name="cls" id="cls" size="3" value="<?php echo $data['cls'] ?>"></td>
                </tr>

                <tr>
                    <td>是否使用</td>
                    <td>
                        <select name="isuse" id="isuse">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value="保存" class="submit1"></td>
                </tr>
            </table>
        </form>


        <script type="text/javascript">

            $(document).ready(function () {

                $("#isuse").val("<?php echo $data['isuse'] ?>");

                $('#formrole').bind('submit', function () {
                    j_post($(this), function (json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


                        /*保存成功*/
                        if ('y' == json.success)
                        {

                            ttt = setTimeout("window.location.href='?pid=<?php echo $data['pid'] ?>'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?pid=<?php echo $data['pid'] ?>">二秒后自动返回角色列表.</a></li>';

                            /*弹出对话框*/
                            opdialog(mess);
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }
    
    
    function accessedit() {
        $j = & $GLOBALS['j'];

        $this->addcrumb('权限编辑');

        $data = $j['access'];
        $group_access = $j['group_access'];
        $id = $j['id'];
        $pid = $j['pid'];

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="formrole">            
            <input type="hidden" name="act" value="accessesave" />       
            <input type="hidden" name="id" value="<?=$id?>" />
            <table class="tableform" cellspacing="1" >
                <tr>
                    <td>
                        分类                      
                    </td>
                    <td>
                        详细权限
                    </td>                  
                </tr>
                <?php 
                foreach($data as $k=>$v){
                    if(in_array($v['id'], $group_access)){
                                $checked='checked="checked"';
                            }else{
                                $checked='';
                    }
                    echo  '<tr><td width="80">';
                    echo $v['title'].'<input type="checkbox" class="iall i'.$v['id'].'" name="access[]" value="'.$v['id'].'" '.$checked.'>';
                    echo "<button type='button' data-id=".$v['id']." data-type='selectall'> 全选</button><br/>";
                    echo "<button type='button' data-id=".$v['id']." data-type='selectnone'> 全不选</button><br/>";
                    echo "<button type='button' data-id=".$v['id']." data-type='unselect'> 反选</button>";
                    echo '</td>  <td width="*"><ul class="accesslist">';
                    if(isset($v['son'])&& is_array($v['son'])){
                        foreach ($v['son'] as $vs){
                            if(in_array($vs['id'], $group_access)){
                                $checked='checked="checked"';
                            }else{
                                $checked='';
                            }
                            echo "<li title='".$vs['name']."'><input type='checkbox' class='iall i".$v['id']."' name='access[]' value='".$vs['id']."' $checked>  ".$vs['id'].$vs['title']  ."</li>";
                        } 
                    }                    
                    echo '</ul></td> </tr>';
                    
                } 
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value="保存" class="submit1"></td>
                </tr>
            </table>
        </form>
        <style>
            .accesslist li{ float: left; width: 33%;/*white-space: nowrap;overflow: hidden;*/}
        </style>
        <script type="text/javascript">
            $(document).ready(function () {
                $("button[type='button']").bind('click',function(){
                    id=$(this).data('id');
                    type=$(this).data('type');
                    
                    if(type==='selectall'){
                        $('.i'+id).prop("checked", true);  
                    }
                    if(type==='selectnone'){
                        $('.i'+id).prop("checked", false);  
                    }
                    if(type==='unselect'){
                       $('.i'+id).each(function () {  
                             $(this).prop("checked", !$(this).prop("checked"));  
                         });
                    }
                    
                    
                });
                
                
                $('#formrole').bind('submit', function () {
                    j_post($(this), function (json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


                        /*保存成功*/
                        if ('y' == json.success)
                        {

                            ttt = setTimeout("window.location.href='?pid=<?php echo $pid ?>'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?pid=<?php echo $pid ?>">二秒后自动返回角色列表.</a></li>';

                            /*弹出对话框*/
                            opdialog(mess);
                        } else { //保存失败，显示失败信息
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
unset($tp);

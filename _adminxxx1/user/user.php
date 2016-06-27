<?php
/* 会员管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
parent::__construct();
        /* 跟据act确定输出格式 */
        $jsonact = array('ischeck','uncheck','islock','unlock','nsave','esave','savepass','del');
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }
        
        require_once AdminApiPath . 'user' . DIRECTORY_SEPARATOR . '_user.php'; //访问接口去

        require_once( adminpath . 'checkpower.php'  ); //检测权限
        $this->addcrumb('用户管理');
        $this->addcrumb('会员管理');

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
            case 'admin': //管理
                $this->fname = 'adminuser';
                require_once( adminpath . 'main.php' ); //主模板
                break;

        }
    }

    /* 会员列表 */

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];

        crumb($this->crumb);
        ?>


        <div class="navoperate">
            <ul>
                <li><a href="?act=creat">添加会员</a></li>
            </ul>
        </div>
        <?php
       // if (false == $list) {
            //$this->showerrlist('暂无记录');
                //return false;
      //  }
       // else
            
            ?>
            <table class="table1 j_list" cellspacing="0">

                <p></p>

                <!--统计信息-->
                <div style="font-weight:bold">               
                    会员总数：<?php
        echo $j['list']['total']
        ?>
            </div>
            <tr>
                <th width="30">ID</th>   
                <th width="*">会员昵称</th>
                <th width="*">会员手机</th>
                <th width="">用户组</th>
                <th width="30">审核</th>
                <th width="30">锁定</th>
                <th width="120">操作</th>
            </tr>

            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['u_nick'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['u_mobile'] . '</td>' . PHP_EOL;

                echo '<td>' . $v['u_gname'] . '</td>' . PHP_EOL;
                echo '<td>' . $this->yesorno($v['ischeck']) . '</td>' . PHP_EOL;
                echo '<td>' . $this->yesorno($v['islock']) . '</td>' . PHP_EOL;
                echo '<td>' . '<a href="?act=edit&amp;id=' . $v['id'] . '">编辑</a>&nbsp; ' . PHP_EOL;
                echo '	<a href="?act=admin&amp;id=' . $v['id'] . '">管理</a>&nbsp; ' . PHP_EOL;
//                echo '	<a href="?act=del&amp;id=' . $v['id'] . '" title="删除' . $v['id'] . '" class="j_confirmdel">删除</a>' . PHP_EOL;
                echo '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>

        </table>
        <?php
        $this->pagelist($j['list']['total']);
    }

    /* 添加会员 */

    function myform() {

        $j = & $GLOBALS['j'];


        $this->addcrumb('添加');
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="nsave" />			
            <table class="table1" cellspacing="0" >

                <tr>
                    <td>会员昵称</td>
                    <td><input type="text" name="u_nick"  value="" size="20" /> <b class="star"></b></td>
                </tr>

                <tr>
                    <td>会员手机</td>
                    <td><input type="text" name="u_mobile" id="u_mobile" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>密码</td>
                    <td><input type="password" name="u_pass" id="u_pass" value="" size="20" /><b class="star">&nbsp;*&nbsp;</b>&nbsp;<span id="divpass" >(6至20位数字或字母)</span></td>
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

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';
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

    function formedit() {
        $this->addcrumb('编辑');
        $j = & $GLOBALS['j'];

        $a_user = $j['thisuser'];

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="esave" />	
            <input type="hidden" name="id" value="<?php echo $a_user['id'] ?>" />
            <table class="table1" cellspacing="0" >

                <tr>
                    <td width="15%">会员昵称</td>
                    <td><input type="text" name="u_nick" id="u_nick" value="<?php echo $a_user['u_nick'] ?>" size="20" /><b class="star">&nbsp;*</b></td>
                </tr>

                <tr>
                    <td>会员手机</td>
                    <td><input type="text" name="u_mobile" id="u_mobile" value="<?php echo $a_user['u_mobile'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value=" 提 交 "  /></td>
                </tr>
            </table>		
        </form>

        <form  method="post" action="?" id="formpass">
            <input type="hidden" name="act" value="savepass" />
            <input type="hidden" name="id" value="<?php echo $a_user['id'] ?>" />
            <table class="table1" cellspacing="0" >
                <tr>
                    <td width="15%">密码</td>
                    <td><input type="password" name="u_pass" id="u_pass" value="" size="20" /><b class="star">&nbsp;*</b>&nbsp;<span id="divpass" >(6至20位数字或字母)</span></td>
                </tr>

                <tr>
                    <td>确认密码</td>
                    <td><input type="password" name="u_pass2" id="u_pass2" value="" size="20" /><b class="star">&nbsp;*</b></td>
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

                            ttt = setTimeout("window.location.href='?ic=user'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?ic=buser">二秒后自动返回列表.</a></li>';


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

                            ttt = setTimeout("window.location.href='?ic=user'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?ic=user">二秒后自动返回列表.</a></li>';


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

    /* 管理会员 */

    function adminuser() {

        $j = & $GLOBALS['j'];

        $id = $j['thisuser']['id'];


        $this->addcrumb('管理');

        crumb($this->crumb);
        ?>

        <table class="table1 j_list" cellspacing="0">


            <tr>
                <td>用户名</td>
                <td><?php echo $j['thisuser']['u_name'] ?></td>
            </tr> 

            <tr>
                <td>审核 <?php echo $this->yesorno($j['thisuser']['ischeck']) ?></td>
                <td>
                    <a href="?act=ischeck&amp;id=<?php echo $id ?>" title="审核通过" class="confirmedit">通过</a>  &nbsp; 
                    <a href="?act=uncheck&amp;id=<?php echo $id ?>" title="未通过审核" class="confirmedit">未过</a>
                </td>
            </tr> 


            <tr>
                <td>锁定 <?php echo $this->yesorno($j['thisuser']['islock']) ?></td>
                <td>
                    <a href="?act=islock&amp;id=<?php echo $id ?>" title="锁定此用户" class="confirmedit">锁定</a> &nbsp; 
                    <a href="?act=unlock&amp;id=<?php echo $id ?>" title="解锁" class="confirmedit">解锁</a>
                </td>
            </tr> 

        </table>

        <script type="text/javascript">
            $(document).ready(function() {
                $('.confirmedit').j_confirmedit(function(json) {
                    /*设置成功刷新页面*/
                    if ('y' == json.success) {
                        document.location.reload();
                    }
                })
            })
        </script>

        <?php
    }

}

$tp = new myclass();
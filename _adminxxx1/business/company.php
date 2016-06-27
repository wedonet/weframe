<?php
/* 店铺管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        require_once AdminApiPath . 'business' . DIRECTORY_SEPARATOR . '_company.php'; //访问接口去
        require_once(adminpath . 'checkpower.php'); //检测权限
        require_once( adminpath . 'checkpower.php' ); //检测权限
        $this->addcrumb('业务管理 ');
        $this->addcrumb('店铺管理');

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
            case 'admin':
                $this->fname = 'formadmin';
                require_once( adminpath . 'main.php' ); //主模板			
                break;


            /* 下面几个不需要渲染 */
            case 'isrun':
            case 'unrun':
            case 'islock':
            case 'unlock':

            case 'nsave':
            case 'esave':
            case 'savepass':
            case 'del':

                break;
        }
    }

    /* 商家列表 */

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];


        crumb($this->crumb);
        ?>


        <div class="navoperate">
            <ul>
                <li><a href="?act=creat">添加店铺</a></li>
            </ul>
        </div>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="40">店铺ID</th>
                <th width="40">店铺IC</th>
                <th width="80">商家用户名</th>
                <th width="*">店铺名称</th>
                <th width="40">用户ID</th>
                <th width="30">运行</th>
                <th width="30">锁定</th>
                <th width="200">操作</th>
            </tr>

            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['u_name'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                if ($v['isrun'] == 1) {
                    echo 'Yes';
                } else {
                    echo 'No';
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                if ($v['islock'] == 1) {
                    echo 'Yes';
                } else {
                    echo 'No';
                }
                echo '</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;

                echo '	<a href="?act=edit&amp;id=' . $v['id'] . '">编辑</a> &nbsp; ' . PHP_EOL;
                echo '  <a href="?act=del&amp;id=' . $v['id'] . '" title="删除店铺' . $v['id'] . '" class="j_del">删除</a> &nbsp;' . PHP_EOL;

                echo ' <a href="../company/goods.php?comid=' . $v['id'] . '">业务</a> &nbsp; ' . PHP_EOL;

                echo ' <a href="?act=admin&amp;id=' . $v['id'] . '">管理</a>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>

        </table>
        <?php
        $this->pagelist($j['list']['total']);
    }

    /* 添加商家 */

    function myform() {

        $j = & $GLOBALS['j'];

        $this->addcrumb('添加');

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="nsave" />	
            <table class="table1" cellspacing="0" >
                <tr>
                    <td>商家用户名</td>
                    <td><input type="text" name="u_name" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b> 从商家用户获取</td>
                </tr>

                <tr>
                    <td>编码</td>
                    <td><input type="text" name="ic" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>

                <tr>
                    <td>店铺名称</td>
                    <td><input type="text" name="title" value="" size="80" /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>


                <tr>
                    <td>预览图</td>
                    <td><input type="text" name="preimg" value="" size="80" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>地址</td>
                    <td><input type="text" name="mylocation" value="" size="80" /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>

                <tr>
                    <td>前台电话</td>
                    <td><input type="text" name="telfront" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b> 座机或手机</td>
                </tr>                
                <tr>
                    <td>开户名</td>
                    <td><input type="text" name="a_name" value="" size="20" /> <b class="star"maxlength="20"  >&nbsp;*&nbsp;</b> </td>
                </tr>
                <tr>
                    <td>开户行</td>
                    <td><input type="text" name="a_bank" value="" size="80" /> <b class="star"maxlength="20"  >&nbsp;*&nbsp;</b> </td>
                </tr>
                <tr>
                    <td>银行账户</td>
                    <td><input type="text" name="a_number" value="" size="80" /> <b class="star"maxlength="25"  >&nbsp;*&nbsp;</b> </td>
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

                            var mess = new Array();

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

    function formedit() {

        $j = & $GLOBALS['j'];


        $data = & $j['data'];


        $this->addcrumb('编辑');

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="esave" />	
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
            <table class="table1" cellspacing="0" >
                
              
                <tr>
                    <td>商家ID</td>
                    <td><?php echo $data['uid'] ?></td>
                </tr>

    
                <tr>
                    <td>编码</td>
                    <td><input type="text" name="ic" value="<?php echo $data['ic'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>

                <tr>
                    <td>名称</td>
                    <td><input type="text" name="title" value="<?php echo $data['title'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>

                <tr>
                    <td>预览图</td>
                    <td><input type="text" name="preimg" value="<?php echo $data['preimg'] ?>" size="80" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>地址</td>
                    <td><input type="text" name="mylocation" value="<?php echo $data['mylocation'] ?>" size="80" /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>

                <tr>
                    <td>前台电话</td>
                    <td><input type="text" name="telfront" value="<?php echo $data['telfront'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b> 座机或手机</td>
                </tr>
                 <tr>
                    <td>开户名</td>
                    <td><input type="text" name="a_name" value="<?php echo $data['a_name'] ?>" size="20"maxlength="20"   /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>
                <tr>
                    <td>开户行</td>
                    <td><input type="text" name="a_bank" value="<?php echo $data['a_bank'] ?>" size="80"maxlength="20"   /> <b class="star">&nbsp;*&nbsp;</b> </td>
                </tr>
                <tr>
                    <td>银行账户</td>
                    <td><input type="text" name="a_number" value="<?php echo $data['a_number'] ?>" size="80" maxlength="25"  /> <b class="star">&nbsp;*&nbsp;</b> </td>
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

                            var mess = new Array();

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

                            var mess = new Array();

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

    /* 管理店铺 */

    function formadmin() {
        $j = & $GLOBALS['j'];

        $id = $j['data']['id'];

        $this->addcrumb('管理');

        crumb($this->crumb);
        ?>




        <table class="table1 j_list" cellspacing="0">
            <tr>
                <td width="20%">店铺IC</td>
                <td><?php echo $j['data']['ic'] ?></td>
            </tr>

            <tr>
                <td>店铺名</td>
                <td><?php echo $j['data']['title'] ?></td>
            </tr> 

            <tr>
                <td>运行 
                    <?php
                    if (1 == $j['data']['isrun']) {
                        echo 'Yes';
                    } else {
                        echo 'No';
                    }
                    ?>
                </td>
                <td>
                    <a href="?act=isrun&amp;id=<?php echo $id ?>" title="运行" class="confirmedit">运行</a>  &nbsp; 
                    <a href="?act=unrun&amp;id=<?php echo $id ?>" title="停止" class="confirmedit">停止</a>
                </td>
            </tr> 


            <tr>
                <td>锁定 
                    <?php
                    if (1 == $j['data']['islock']) {
                        echo 'Yes';
                    } else {
                        echo 'No';
                    }
                    ?>

                </td>
                <td>
                    <a href="?act=islock&amp;id=<?php echo $id ?>" title="锁定店铺" class="confirmedit">锁定店铺</a> &nbsp; 
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
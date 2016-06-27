<?php
/* 用户组管理 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

/* 请求数据 */

class myclass extends cls_template {

    function __construct() {

        require_once (AdminApiPath . 'user' . DIRECTORY_SEPARATOR . '_group.php'); //访问接口去

        require_once( adminpath . 'checkpower.php'  ); //检测权限
        $this->addcrumb('用户管理');
        $this->addcrumb('用户组');

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
            case 'esave':
            case 'del':
                break;
        }
    }

    function mylist() {
        $data = & $GLOBALS['j'];

        crumb($this->crumb);
        ?>


        <div class="navoperate">
            <ul>
                <li><a href="?act=creat&amp;mytype=group">添加新用户组</a></li>
            </ul>
        </div>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="30">ID</th>
                <th width="*">名称</th>
                <th width="120">识别码</th>

                <th width="40">角色数</th>
                <th width="40">会员数</th>


                <th width="40">排序</th>
                <th width="40">使用</th>
                <th width="150">操作</th>
            </tr>

            <?php
            foreach ($data['grouplist'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['countson'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['countuser'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['cls'] . '</td>' . PHP_EOL;
                echo '<td>' . $this->yesorno($v['isuse']) . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                echo '	<a href="?act=edit&amp;mytype=group&amp;id=' . $v['id'] . '">编辑</a> &nbsp; ';
                echo '	<a href="role.php?mytype=role&amp;pid=' . $v['id'] . '">角色</a> &nbsp; ';
                echo '	<a href="?act=del&amp;id=' . $v['id'] . '" title="删除' . $v['title'] . '" class="j_del">删除</a>' . PHP_EOL;
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

        $data['mytype'] = $this->get('mytype'); //用户组还是角色		


        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="nsave" />       

            <input type="hidden" name="mytype" value="group" />

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
                    <td><input type="submit" name="submit" value="保存"></td>
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

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回用户组列表.</a></li>';
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

    /* 编辑用户组 */

    function formedit() {
        $j = & $GLOBALS['j'];

        $this->addcrumb('编辑');

        $data = $j['group'];

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="esave" />       
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
            <input type="hidden" name="mytype" value="group" />

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
                    <td><input type="submit" name="submit" value="保存"></td>
                </tr>
            </table>
        </form>


        <script type="text/javascript">

            $(document).ready(function () {

                $("#isuse").val("<?php echo $data['isuse'] ?>");

                $('#myform').bind('submit', function () {
                    j_post($(this), function (json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


                        /*保存成功*/
                        if ('y' == json.success)
                        {

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回用户组列表.</a></li>';

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

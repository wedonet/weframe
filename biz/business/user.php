<?php
/* 商品管理 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');

/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        require_once(ApiPath . '/biz/business/_user.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb($j['company']['title']); //crumb加上公司名

        $this->addcrumb('操作员管理');

        $this->act = $this->ract();

        switch ($this->act) {
                case'':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            case 'edit':
                $this->fname = 'formedit';
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            case 'creat':
                $this->fname = 'myform';
                 require_once( syspath . '/biz/main.php' ); //主模板
                break;
            
            case 'esave':
            case 'nsave':
                break;
        }
    }

    /* 操作员列表 */
 function mylist() {

        $j = & $GLOBALS['j'];

        $comid = $this->comid;

        crumb($this->crumb);

        ?>
        <div class="navoperate">
            <ul>
                <li><a id="addself" href="?act=creat&amp;comid=<?php echo $comid ?>">添加操作员</a> &nbsp; </li>
            </ul>
        </div>

        <table class="table1 j_list" cellspacing="0">
            <tr>

                <th width="80">操作员ID</th>
                <th width="80">用户名</th>
                <th width="80">真实姓名</th>
                <th width="*">注册时间</th>

                <th width="*">是否锁定</th> 
                <th width="*">角色</th>
                <th width="200">操作</th>

            </tr>

        <?php
        foreach ($j['list'] as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;

            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['u_name'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['u_fullname'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;

            echo '<td>' . $this->yesorno($v['islock']) . '</td>' . PHP_EOL;
            echo '<td>' . $v['u_rolename'] . '</td>' . PHP_EOL;

            echo '<td>' . PHP_EOL;
            echo '	<a href="?act=edit&amp;comid=' . $comid . '&amp;id=' . $v['id'] . '">修改</a> &nbsp; ';
            echo '	<a href="?act=del&amp;comid=' . $comid . '&amp;id=' . $v['id'] . '" title="删除' . $v['u_name'] . '" class="j_del">删除</a>';
            echo '</td>' . PHP_EOL;

            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>

        <?php
    }

    function myform() {
        $j = & $GLOBALS['j'];

        $comid = $this->comid;
        $role = &$j['role'];
        $this->addcrumb('添加');

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="nsave" />		
            <input type="hidden" name="comid" value="<?php echo $comid ?>" />	

            <table class="table1" cellspacing="0" >

                <tr>
                    <td>用户名</td>
                    <td><input type="text" name="u_name" id="u_name" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b> (6至20位数字或字母组合)</td>
                </tr>

                <tr>
                    <td>真实姓名</td>

                    <td><input type="text" name="u_fullname" id="u_fullname" value="" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>

                </tr>
                <tr>
                    <td>锁定</td>
                    <td>
                        <select name="islock" id="islock">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>角色</td>
                    <td>
                        <select name="u_roleic" id="u_roleic">
                            <option value="" selected="selected">请选择角色</option>
        <?php
        foreach ($role as $val) {
            echo '<option value="' . $val['ic'] . '">' . $val['title'] . '</option>' . PHP_EOL;
        }
        ?>
                        </select>
                        <b class="star">&nbsp;*&nbsp;</b> 
                    </td>
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

                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?comid=<?php echo $comid ?>'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?comid=<?php echo $comid ?>">二秒后自动返回列表.</a></li>';
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

        $j = & $GLOBALS['j'];
        
        if ('y' != $j['success']) {
            $this->showerrlist($j['errmsg']); //把错误信息打印出来
            die();
        } 

        $comid = $this->comid;

        $data = $j['data'];
        $role = $j['role'];
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="esave" />	
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
            <table class="table1" cellspacing="0" >

                <tr>
                    <td width="15%">用户名</td>
                    <td><?php echo $data['u_name'] ?></td>
                </tr>

                <tr>
                    <td>真实姓名</td>
                    <td><input type="text" name="u_fullname" id="u_fullname" value="<?php echo $data['u_fullname'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>锁定</td>
                    <td>
                        <select name="islock" id="islock">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>角色</td>
                    <td>
                        <select name="u_roleic" id="u_roleic">
                            <option value="" selected="selected">请选择角色</option>
        <?php
        foreach ($role as $val) {
            echo '<option value="' . $val['ic'] . '">' . $val['title'] . '</option>' . PHP_EOL;
        }
        ?>
                        </select>
                        <b class="star">&nbsp;*&nbsp;</b> 
                    </td>
                </tr>

                <tr>
                    <td>联系电话</td>
                    <td><input type="text" name="u_phone" id="u_phone" value="<?php echo $data['u_phone'] ?>" size="20" /></td>
                </tr>

                <tr>
                    <td>手机</td>
                    <td><input type="text" name="u_mobile" id="u_mobile" value="<?php echo $data['u_mobile'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>电子邮箱</td>
                    <td><input type="text" name="u_mail" id="u_mail" value="<?php echo $data['u_mail'] ?>" size="20" /></td>
                </tr>


                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value=" 保 存 "  /></td>
                </tr>
            </table>		
        </form>

        <p></p>
        <form  method="post" action="?" id="formpass">
            <input type="hidden" name="act" value="savepass" />
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
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
                    <td><input type="submit" value=" 保 存 "  /></td>
                </tr>
            </table>


        </form>

        <script type="text/javascript">

            $(document).ready(function() {
                /*更新角色*/
                $('#u_roleic').val('<?php echo $data["u_roleic"] ?>');
                $('#islock').val('<?php echo $data["islock"] ?>');
                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {

                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/
                            var hreflist = '?comid=<?php echo $comid ?>';

                            ttt = setTimeout("window.location.href='" + hreflist + "'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="' + hreflist + '">二秒后自动返回列表.</a></li>';


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

                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/
                            var hreflist = '?comid=<?php echo $comid ?>';

                            ttt = setTimeout("window.location.href='" + hreflist + "'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="' + hreflist + '">二秒后自动返回列表.</a></li>';


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

$tp = new myclass(); //调用类的实例
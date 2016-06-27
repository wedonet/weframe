<?php
/* 管理员管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        require_once AdminApiPath . 'user' . DIRECTORY_SEPARATOR . '_access_mange.php'; //访问接口去
        require_once(adminpath . 'checkpower.php'); //检测权限

        $this->addcrumb('权限管理');
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
//            case 'admin': //管理
//                $this->fname = 'adminadmin';
//                require_once( adminpath . 'main.php' ); //主模板
//                break;
        }
    }

    /* 管理员列表 */

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['accesslist'];
//      print_r($list);
        crumb($this->crumb);
        ?>


        <div class="navoperate">
            <ul>
                <li><a href="?act=creat">增加操作权限</a></li>
            </ul>
        </div>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="10%">ID</th>
                <th width="30%">操作名</th>
                <th width="40%">操作描述</th>                
                <th width="10%">排序</th>                
                <th width="">操作</th>
            </tr>
            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent" data-id="' . $v['id'] . '">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['name'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['cls'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                echo '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
                if (!empty($v['son']) && is_array($v['son'])) {
                    foreach ($v['son'] as $vs) {
                        echo '<tr class="j_son j_parent s' . $v['id'] . '">' . PHP_EOL;
                        echo '<td>&nbsp;|--' . $vs['id'] . '</td>' . PHP_EOL;
                        echo '<td>&nbsp;|-- ' . $vs['name'] . '</td>' . PHP_EOL;
                        echo '<td>&nbsp;|--' . $vs['title'] . '</td>' . PHP_EOL;
                        echo '<td>&nbsp;|--' . $vs['cls'] . '</td>' . PHP_EOL;
                        echo '<td>' . '<a href="?act=edit&amp;id=' . $vs['id'] . '">编辑</a>&nbsp; ' . PHP_EOL;
                        echo '<a href="?act=del&amp;id=' . $vs['id'] . '" title="是否删除" class="j_del" >删除</a>&nbsp; ' . PHP_EOL;
                        echo '</td>' . PHP_EOL;
                        echo '</tr>' . PHP_EOL;
                    }
                }
            }
            ?>

        </table>
        <style>
            .j_son{display: none;}
        </style>
        <script>
            $(".j_parent").bind("click", function () {
                tid = $(this).data('id');
        //   alert($(".s"+tid));
                $('.s' + tid).toggle();
            })
        </script>

        <?php
    }

    /* 添加管理员 */

    function myform() {
        $j = & $GLOBALS['j'];
        $this->addcrumb('添加');
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="nsave" />			
            <table class="table1" cellspacing="0" >
                <tr>
                    <td>分类名称</td>
                    <td>
                        <select name="pid">
                            <option value="">请选择分类</option>
                            <?php
                            foreach ($j['access_type'] as $v) {
                                echo '<option value="' . $v['id'] . '">' . $v['title'] . '</option>' . PHP_EOL;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>名称</td>
                    <td>
                        <input type="text" name="name" id="name" value="" size="40" maxlength="100" /> <b class="star">&nbsp;*&nbsp;</b>
                    </td>
                </tr>
                <tr>
                    <td>描述</td>
                    <td><input type="text" name="title"  value="" size="60" maxlength="200" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td>排序(数值)</td>
                    <td><input type="text" name="cls" id="cls" value="10" size="20" maxlength="20"  /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value=" 提 交 "  /></td>
                </tr>
            </table>		
        </form>
        <script type="text/javascript">

            $(document).ready(function () {

                $('#myform').bind('submit', function () {
                    j_post($(this), function (json) {
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

    function formedit() {
        $j = & $GLOBALS['j'];
        
        if(empty($j['access_info'])){
            echo "参数错误！";
            exit();
        }
        $info=$j['access_info'];
        $this->addcrumb('添加');
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="esave" />			
            <input type="hidden" name="id" value="<?=$info['id']?>" />			
            <table class="table1" cellspacing="0" >
                <tr>
                    <td>分类名称</td>
                    <td>
                        <select name="pid">
                            <option value="">请选择分类</option>
                            <?php
                            foreach ($j['access_type'] as $v) {
                                if($v['id']==$info['pid']){
                                    $select='selected="selected" ';
                                }else{
                                    $select='';
                                }
//                                echo $select;
                                echo '<option value="' . $v['id'] . '"  '.$select.'>' . $v['title'] . '</option>' . PHP_EOL;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>名称</td>
                    <td>
                        <input type="text" name="name" id="name" value="<?=$info['name']?>" size="40" maxlength="100" /> <b class="star">&nbsp;*&nbsp;</b>
                    </td>
                </tr>
                <tr>
                    <td>描述</td>
                    <td><input type="text" name="title"  value="<?=$info['title']?>" size="60" maxlength="200" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td>排序(数值)</td>
                    <td><input type="text" name="cls" id="cls" value="<?=$info['cls']?>" size="20" maxlength="20"  /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value=" 提 交 "  /></td>
                </tr>
            </table>		
        </form>
        <script type="text/javascript">

            $(document).ready(function () {

                $('#myform').bind('submit', function () {
                    j_post($(this), function (json) {
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

    /* 管理管理员 */

    function adminadmin() {

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
            $(document).ready(function () {
                $('.confirmedit').j_confirmedit(function (json) {
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

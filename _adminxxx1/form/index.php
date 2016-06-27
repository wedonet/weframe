<?php
/* 管理中心 - 神灯替我付 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 跟据act确定输出格式 */
        $jsonact = array('nsave', 'esave',
            'doing',
            'stop',
            'delete'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once AdminApiPath . 'form' . DIRECTORY_SEPARATOR . '_index.php'; //访问接口去

        require_once( adminpath . 'checkpower.php'); //检测权限

        $this->addcrumb('<a href="?">调查管理</a>');



        switch ($this->act) {
            case '' :
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
            case 'creat':
                $this->fname = 'myform'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
            case 'edit':
                $this->fname = 'formedit'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
        }
    }

    /* function  showlist()
      {
      $j=&$GLOBALS['j'];
      $list=&$j['list']['rs'];
      $total=& $j['list']['total'];
      crumb($this->crumb);

      } */

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];
        $total = & $j['list']['total'];

        crumb($this->crumb);
        ?>
        <div class="navoperate">
            <ul>
                <li><a href="?act=creat">添加新调查</a></li>
            </ul>
        </div>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="50">ID</th>
                <th width="50">IC</th>
                <th width="*">名称</th>
                <th width="80">调查数量</th>
                <th width="80">已完成数量</th>
                <th width="80">提交人</th>
                <th width="100">提交时间</th>
                <th width="50">状态</th>
                <th width="160">操作</th>
            </tr>

            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['mycount'] . '</td>' . PHP_EOL;

                echo '<td>' . $v['donecount'] . '</td>' . PHP_EOL;

                echo '<td>' . $v['unick'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['mystatusname'] . '</td>' . PHP_EOL;

                echo '<td> ' . PHP_EOL;

                if ('doing' == $v['mystatus']) {
                    echo '  <a href="?act=stop&amp;id=' . $v['id'] . '" class="j_do" title="停止">停止</a> &nbsp; ' . PHP_EOL;
                } else {
                    echo '  <a href="?act=doing&amp;id=' . $v['id'] . '" class="j_do" title="运行">运行</a> &nbsp; ' . PHP_EOL;
                }

                echo '  <a href="?act=edit&amp;id=' . $v['id'] . '">编辑</a>&nbsp; ' . PHP_EOL;

                echo '  <a href="answer/' . $v['ic'] . '.php?&amp;ic=' . $v['ic'] . '&amp;id=' . $v['id'] . '">查看答案</a> &nbsp' . PHP_EOL;

                echo '  <a href="?act=delete&amp;id=' . $v['id'] . '" class="j_do alarm" title="删除">删除</a>' . PHP_EOL;
                echo '</td>' . PHP_EOL;

                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>

        <script type="text/javascript">
        <!--
            $(document).ready(function () {
                $('a.j_do').j_do(function (json) {
                    if ('n' == json.success) {
                        errdialog(json);
                    } else {
                        document.location.reload();
                    }
                })
            })
        //-->
        </script>

        <?php
        $this->pagelist($total);
    }

    function myform() {
        $this->addcrumb('<a href="?">添加新调查</a>');

        crumb($this->crumb);
        ?>
        <form method="post" action="?act=nsave" id="myform">  
            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="70">IC</td>
                    <td width="*"><input type="text" name="ic" id="ic" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="70">广告商IC</td>
                    <td width="*"><input type="text" name="aic" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>


                <tr>
                    <td width="70">调查名称</td>
                    <td width="*"><input type="text" name="title" value="" maxlength="14" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="70">调查说明</td>
                    <td width="*"><input type="text" name="readme" value="" maxlength="20" size="20"><b class="star"></b></td>
                </tr>

                <tr>
                    <td width="80">每份调查返款</td>
                    <td width="*"><input type="text" name="myvalue" value="" size="20">元<b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="70">调查数量</td>
                    <td width="*"><input type="text" name="mycount" maxlength="7" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="70">题数</td>
                    <td width="*"><input type="text" name="questioncount" maxlength="7" value="" size="20">道<b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                </tr>
                <tr>
                    <td width="70">预计答题时间</td>
                    <td width="*"><input type="text" name="plantime" maxlength="7"value="" size="20">分钟<b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="70">到期时间</td>
                    <td width="*"><input type="text" name="overtime" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="提交" disabled="disabled" class="slowsubmit"></td>
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

                            mess['content'] = '<li><a href="?">二秒后自动返回调查管理.</a></li>';


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
        $this->addcrumb('编辑');
        $j = & $GLOBALS['j'];

        $a_form = $j['data'];

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="esave" />	
            <input type="hidden" name="id" value="<?php echo $a_form['id'] ?>" />

            <table class="table1" cellspacing="0" >

                <tr>
                    <td width="70">IC</td>
                    <td width="*"><input type="text" name="ic" id="ic" value="<?php echo $a_form['ic'] ?>" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="70">广告商IC</td>
                    <td width="*"><?php echo $a_form['aic'] ?></td>
                </tr>


                <tr>
                    <td width="70">调查名称</td>
                    <td width="*"><input type="text" name="title" value="<?php echo $a_form['title'] ?>" maxlength="14" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="70">调查说明</td>
                    <td width="*"><input type="text" name="readme" value="<?php echo $a_form['readme'] ?>" maxlength="20" size="20"><b class="star"></b></td>
                </tr>

                <tr>
                    <td width="80">每份调查返款</td>
                    <td width="*"><input type="text" name="myvalue" value="<?php echo $a_form['myvalue'] / 100 ?>" size="20">元<b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="70">调查数量</td>
                    <td width="*"><input type="text" name="mycount" maxlength="7" value="<?php echo $a_form['mycount'] ?>" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="70">题数</td>
                    <td width="*"><input type="text" name="questioncount" maxlength="7" value="<?php echo $a_form['questioncount'] ?>" size="20">道<b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                </tr>
                <tr>
                    <td width="70">预计答题时间</td>
                    <td width="*"><input type="text" name="plantime" maxlength="7"value="<?php echo $a_form['plantime'] ?>" size="20">分钟<b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td width="70">到期时间</td>
                    <td width="*"><input type="text" name="overtime" value="<?php echo date('Y-m-d', $a_form['overtime']) ?>" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="提交" disabled="disabled" class="slowsubmit"></td>
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

                            mess['content'] = '<li><a href="?">二秒后自动返回调查管理.</a></li>';


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

    function showerr(&$j) {
        foreach ($j['errmsg'] as $v) {
            echo $v;
        }
    }

}

$tp = new myclass(); //调用类的实例
unset($tp);

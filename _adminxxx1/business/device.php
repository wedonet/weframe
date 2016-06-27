<?php
/* 店铺管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* ============================== */
        /* 什么情况下必须返回json格式 */

        $jsonact = array('json'
            , 'isrun'
            , 'unrun'
            , 'islock'
            , 'unlock'
            , 'nsave'
            , 'esave'
            , 'savepass'
            , 'del'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once AdminApiPath . 'business' . DIRECTORY_SEPARATOR . '_device.php'; //访问接口去
        require_once(adminpath . 'checkpower.php'); //检测权限

        $this->addcrumb('业务管理');
        $this->addcrumb('<a href="?">设备管理</a>');

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

//
//            /* 下面几个不需要渲染 */
//            case 'isrun':
//            case 'unrun':
//            case 'islock':
//            case 'unlock':
//
//            case 'nsave':
//            case 'esave':
//            case 'savepass':
//            case 'del':
//
//                break;
        }
    }

    /* 商家列表 */

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $jsonstr = json_encode($j['search']); //将数组字符串化

        crumb($this->crumb);
        ?>
        <div class="navoperate">
            <ul>
                <li><a id="addself" href="?act=creat">添加设备</a> &nbsp; </li>
            </ul>
        </div>
        <!--搜索-->
        <div class="listfilter">
            <form id="formsearch" style="display:inline" action="?" method="get">
                <input type="text"  name="ic" value="<?php echo $j['search']['ic'] ?>" placeholder="设备IC"/> 
                <input type="text"  name="comtitle" value="<?php echo $j['search']['comtitle'] ?>" placeholder="所属店铺"/> 
                <select name="mystatus" id="mystatus">
                    <option value="">全部状态</option>
                    <option value="unline">未连接</option>
                    <option value="doing">连接</option>
                    <option value="down">断开</option>

                </select>
                <input id="btnsearch" type="submit" value="搜索" />
            </form>
        </div>

        <!--将选中数据的值更新到#mystatus-->
        <script type="text/javascript">
            $(document).ready(function() {

                var jsonstr = <?php echo $jsonstr ?>;//json数据,无''
                //alert(jsonstr.mystatus);
                $('#mystatus').val(jsonstr.mystatus);

            })

        </script>
        <?php
        if (false == $list['rs']) {
            echo '没有满足搜索条件的数据';
        } else {
            ?>

            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="40">ID</th>
                    <th width="40">IC</th>
                    <th width="*">机型</th>
                    <th width="*">门数</th>
                    <th width="*">是否运行</th>

                    <th width="">所属店铺</th>

                    <th width="">状态</th>




                    <th width="150">操作</th>
                </tr>

                <?php
                foreach ($list['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['typeic'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['doornum'] . '</td>' . PHP_EOL;

                    echo '<td>' . $this->yesorno($v['isrun']) . '</td>' . PHP_EOL;

                    echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
                    echo '<td>'
                    ?><?php
                    switch ($v['mystatus']) {
                        case '':
                            echo '未连接';
                            break;
                        case 'doing':
                            echo '连接';
                            break;
                        case 'down':
                            echo '断开';
                            break;
                    }'</td>' . PHP_EOL;

                    echo '<td>' . PHP_EOL;
                    echo '  <a href="?act=admin&amp;id=' . $v['id'] . '">管理</a> &nbsp; ' . PHP_EOL;
                    echo '  <a href="?act=edit&amp;id=' . $v['id'] . '">编辑</a> &nbsp;' . PHP_EOL;
                    echo '  <a href="?act=del&amp;id=' . $v['id'] . '" title="删除ID为' . $v['id'] . '的设备?" class="j_del">删除</a>
                    </td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
                ?>
            </table>


            <?php
            $this->pagelist($j['list']['total']);
        }
    }

    /* 添加商家 */

    function myform() {

        $j = & $GLOBALS['j'];

        $this->addcrumb('添加');

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="nsave" />  


            <table class="tableform" cellspacing="1" >

                <tr>
                    <td width="60">机型</td>
                    <td width="*"><input type="text" name="typeic" id="typeic" value="" size="20"><b class="star">&nbsp;*&nbsp;</b></td>
                </tr>     

                <tr>
                    <td>设备IC</td>
                    <td><input type="text" name="ic" id="ic" size="20" value=""><b class="star">&nbsp;*&nbsp;</b></td>
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

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回设备列表!</a></li>';


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


            <table class="tableform" cellspacing="1" >

                <tr>
                    <td width="60">ID</td>
                    <td width="*"><?php echo $data['id'] ?></td>
                </tr>

                <tr>
                    <td width="60">机型</td>
                    <td width="*"><?php echo $data['typeic'] ?></td>
                </tr>     

                <tr>
                    <td width="60">门数</td>
                    <td width="*"><?php echo $data['doornum'] ?></td>
                </tr>      

                <tr>
                    <td>位置IC</td>
                    <td><?php echo $data['placeic'] ?></td>
                </tr>

                <tr>
                    <td>位置ID</td>
                    <td><?php echo $data['placeid'] ?></td>
                </tr>

                <tr>
                    <td>是否运行</td>

                    <td><?php
                        if (0 == $data['isrun']) {
                            echo '停止';
                        } else {
                            echo '运行';
                        }
                        ?></td>
                </tr>   

                <tr>
                    <td>设备IC</td>
                    <td><input type="text" name="ic" id="ic" size="20" value="<?php echo $data['ic'] ?>"><b class="star">&nbsp;*</b></td>
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


                        /*保存成功，跳转到支付页*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?ic=bizer'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回设备列表！</a></li>';


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

        $data = $j['data'];

        crumb($this->crumb);
        ?>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <td width="20%">设备ID</td>
                <td><?php echo $j['data']['id'] ?></td>
            </tr>

            <tr>
                <td width="20%">设备IC</td>
                <td><?php echo $j['data']['ic'] ?></td>
            </tr>

            <tr>
                <td>机型</td>
                <td><?php echo $j['data']['typeic'] ?></td>
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
                <td>所属店铺</td>
                <td>
                    <form method="post" action="?" id="formadmin">
                        <input type="hidden" name="act" value="belong" />
                        <input type="hidden" name="id" value="<?php echo $id ?>" />
                        店铺ID:<input type="text" name="comid" value="<?php echo $data['comid'] ?>" /><b class="star">&nbsp;*</b>&nbsp;&nbsp; 
                        店铺IC:<input type="text" name="comic" value="<?php echo $data['comic'] ?>" /> &nbsp;

                        <input type="submit" value="保存" />
                    </form>
                </td>
            </tr> 
        </table>

        <div class="tip1">
            <dl>
                <dt>说明</dt>
                <dd>如需将设备移出店铺，请将ID设为0，IC设为空</dd>
            </dl>
        </div>

        <script type="text/javascript">
            $(document).ready(function() {

                $('#formadmin').bind('submit', function() {
                    j_post($(this), function(json) {

                        /*保存成功*/
                        if ('y' == json.success)
                        {

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回设备列表！</a></li>';

                            /*弹出对话框*/
                            opdialog(mess);
                        }
                        else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })


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
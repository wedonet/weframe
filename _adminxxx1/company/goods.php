<?php
/* 商品管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 什么情况下必须返回json格式 */
        $jsonact = array(
            'savealarm',
            'dotoplat'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }


        /* 提取数据 */
        require_once(AdminApiPath . 'company/_goods.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb('<a href="goods.php?comid=' . $this->comid . '">' . $j['company']['title'] . '</a>'); //crumb加上公司名

        $this->addcrumb('商品管理');





        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;

            case 'select':
                $this->fname = 'selectgoods'; //主内容区
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
            case 'alarm':
                $this->formalarm();
                break;
            case 'toplat':
                $this->formtoplat();
                break;
        }
    }

    /* 商家列表 */

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $comid = $this->get('comid');

        crumb($this->crumb);

        require_once('biztab.php'); /* 商家业务选项卡 */

        $this->goodstab($comid); //商品管理选项卡
        ?>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">店铺商品ID</th>
                <th width="50">商品ID</th>
                <th width="*">名称</th>
                <th width="*">警戒库存</th>
                <th width="50">剩余</th>
                <th width="240">价格/佣金（元）</th>

                <th width="40">所属</th>
                <th width="100">操作</th>
            </tr>

            <?php
            foreach ($list as $v) {
                /* 保存价格的地址 */
                $hrefsave = '?act=saveprice&amp;comid=' . $comid . '&amp;id=' . $v['id'];

                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['goodsid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['title'] . (1 == $v['isgroup'] ? ' <span class="red">[组合]</span>' : '') . '</td>' . PHP_EOL;

                echo '<td>';
                if (0 == $v['isgroup']) {
                    echo '  <a href="?act=alarm&amp;id=' . $v['id'] . '" class="j_open"> &nbsp; ' . $v['inventoriesalarm'] . ' &nbsp; </a>';
                }
                echo '</td>' . PHP_EOL;

                echo '<td>' . $v['inventories'] . '</td>' . PHP_EOL;

                echo '<td>
					<input name="price" value="' . $v['price'] / 100 . '" size="10" />
					<input name="commission" value="' . $v['commission'] / 100 . '" size="10" />
					<input type="hidden" name="comid" value="' . $comid . '">
					<a href="' . $hrefsave . '" class="j_saveinput"><span>保存</span></a>
					</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;
                if (0 == $v['comid']) {
                    echo '平台';
                } else {
                    echo '自营';
                }
                echo '</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;

                /* 删除平台自营商品 */
                if (0 == $v['comid']) {
                    echo '  <a href="?act=del&amp;comid=' . $comid . '&amp;id=' . $v['id'] . '" title="删除' . $v['title'] . '" class="j_del">删除</a> &nbsp; ' . PHP_EOL;
                }

                /* 单品可以恳回平台 */
                if (0 == $v['isgroup']) {
                    echo '  <a href="?act=toplat&amp;comid=' . $comid . '&amp;id=' . $v['id'] . '" class="j_open">退回平台</a></td>' . PHP_EOL;
                }
                echo '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>

        <script>
            $(document).ready(function () {
                /*保存价格*/
                $('a.j_saveinput').bind('click', function () {
                    var obj = $(this);

                    var url = $(this).attr('href');

                    /*追加上现在的价格*/
                    var price = $(this).siblings('input[name=price]').val();
                    var commission = $(this).siblings('input[name=commission]').val();
                    var comid = $(this).siblings('input[name=comid]').val();
                    //alert(comid);
                    url += ('&price=' + price);
                    url += ('&commission=' + commission);


                    /*变为loading*/
                    obj.hide().after('<span id="myloading">Saving</span>');


                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: url,
                        dataType: 'json', //返回json格式数据
                        success: function (json) {

                            /*保存成功*/
                            if ('y' == json.success)
                            {


                                ttt = setTimeout(function () {
                                    $('#myloading').remove();
                                    obj.show();
                                }, 500);
                                //obj.show();//还原保存的链接按钮


                                //$('#myloading').remove();

                                //ttt = setTimeout("window.location.href='?comid="+comid+"'", 1000);

                                //var mess=new Array(); 

                                //mess['content'] = '<li>保存成功.</li>';
                                // opdialog(mess);
                            } else { //保存失败，显示失败信息
                                errdialog(json);

                                obj.show();//还原保存的链接按钮

                                $('#myloading').remove();
                            }

                        },
                        error: function (xhr, type, error) {
                            alert('Ajax error:' + xhr.responseText);
                        }
                    })

                    return false;
                })
            })
        </script>

        <?php
    }

    /* 选择商品 */

    function selectgoods() {

        $j = & $GLOBALS['j'];

        $this->addcrumb('选择商品');

        crumb($this->crumb);

        $comid = $j['company']['id'];

        require_once('biztab.php');

        $this->goodstab($comid); //商品管理选项卡
        ?>




        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="savegoods" />	
            <input type="hidden" name="comid" value="<?php echo $this->addcrumb($j['company']['id']) ?>" />


            <!--列出全部商品-->	
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="40">商品ID</th>
                    <th width="40">商品IC</th>
                    <th width="*">名称</th>


                    <th width="180">操作</th>
                </tr>				

                <?php
                foreach ($j['list'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['title'] . '</td>' . PHP_EOL;

                    echo '<td>' . PHP_EOL;
                    echo '	<a href="?act=sell&amp;comid=' . $comid . '&amp;id=' . $v['id'] . '" class="j_del">出售</a> &nbsp; ' . PHP_EOL;

                    echo '</td>' . PHP_EOL;


                    echo '</tr>' . PHP_EOL;
                }
                ?>

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

                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';


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
        require_once(AdminApiPath . 'user/bizer.php');

        $j = & $GLOBALS['j'];

        $a_user = $j['user'];

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="esave" />
            <input type="hidden" name="u_gic" value="bizer" />
            <input type="hidden" name="id" value="<?php echo $a_user['id'] ?>" />
            <table class="table1" cellspacing="0" >

                <tr>
                    <td width="15%">用户名</td>
                    <td><?php echo $a_user['u_name'] ?></td>
                </tr>

                <tr>
                    <td>联系电话</td>
                    <td><input type="text" name="u_phone" id="u_phone" value="<?php echo $a_user['u_phone'] ?>" size="20" /></td>
                </tr>

                <tr>
                    <td>手机</td>
                    <td><input type="text" name="u_mobile" id="u_mobile" value="<?php echo $a_user['u_mobile'] ?>" size="20" /> <b class="star">&nbsp;*&nbsp;</b></td>
                </tr>

                <tr>
                    <td>电子邮箱</td>
                    <td><input type="text" name="u_mail" id="u_mail" value="<?php echo $a_user['u_mail'] ?>" size="20" /></td>
                </tr>


                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value=" 提 交 "  /></td>
                </tr>
            </table>		
        </form>

        <p></p><p></p>
        <form  method="post" action="?" id="formpass">
            <input type="hidden" name="act" value="savepass" />
            <input type="hidden" name="id" value="<?php echo $a_user['id'] ?>" />
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

                            ttt = setTimeout("window.location.href='?ic=bizer'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?ic=bizer">二秒后自动返回列表.</a></li>';


                            /*弹出对话框*/
                            opdialog(mess);
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })


                $('#formpass').bind('submit', function () {
                    j_post($(this), function (json) {
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

    /* 管理店铺 */

    function formadmin() {
        /* 访问用户接口 */
        //$_GET['ic'] = 'bizer'; //通知接口取商家信息
        require_once(AdminApiPath . 'business/company.php');

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
                <td>运行 <?php echo $j['data']['isrun'] ?></td>
                <td>
                    <a href="?act=isrun&amp;id=<?php echo $id ?>" title="运行" class="confirmedit">运行</a>  &nbsp; 
                    <a href="?act=unrun&amp;id=<?php echo $id ?>" title="停止" class="confirmedit">停止</a>
                </td>
            </tr> 


            <tr>
                <td>锁定 <?php echo $j['data']['islock'] ?></td>
                <td>
                    <a href="?act=islock&amp;id=<?php echo $id ?>" title="锁定店铺" class="confirmedit">锁定店铺</a> &nbsp; 
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

    function formalarm() {
        $j = & $GLOBALS['j'];
        ?>
        <form method="post" action="?act=savealarm&amp;id=<?php echo $this->get('id') ?>" id="myform" style="width:500px;">
            <table class="table1" cellspacing="0" >
                <tr><th colspan="2">警戒库存</th></tr>  

                <tr>                 
                    <td>警戒库存量</td>    
                    <td><input name="inventoriesalarm" size="6" maxlength="7" value="<?php echo $j['data']['inventoriesalarm'] ?>" /></td>    
                </tr>                

                <tr> 
                    <td colspan="2"><input type="submit" name="submit" value=" 提 交 " class="submit"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function () {

                $('#myform').on('submit', function () {
                    j_repost($(this), function (json) {
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            document.location.reload();
                        } else { //保存失败，显示失败信息
                            showerrdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    /* 退回平台表 */

    function formtoplat() {
        $j = & $GLOBALS['j'];
        ?>
        <form method="post" action="?act=dotoplat&amp;comid<?php echo $this->comid ?>&amp;id=<?php echo $this->get('id') ?>" id="myform" style="width:500px;">
            <table class="table1" cellspacing="0" >
                <tr><th colspan="2">退回平台</th></tr>  

                <tr>                 
                    <td>名称</td>    
                    <td><?php echo $j['data']['title'] ?></td>    
                </tr>  

                <tr>                 
                    <td>剩余</td>    
                    <td><?php echo $j['data']['inventories'] ?></td>    
                </tr> 

                <tr>                 
                    <td>凭证号</td>    
                    <td><input name="formcode" size="20" value="" /></td>
                </tr>   

                <tr>                 
                    <td>退回数量</td>    
                    <td><input name="mycount" size="6"  maxlength="7" value=""  /></td>    
                </tr>                

                <tr> 
                    <td colspan="2"><input type="submit" name="submit" value=" 提 交 " class="submit"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function () {

                $('#myform').on('submit', function () {
                    j_repost($(this), function (json) {
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            document.location.reload();
                        } else { //保存失败，显示失败信息
                            showerrdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    /* 商品管理选项卡 */

    function goodstab($comid) {
        ?>
        <div class="navoperate">
            <ul>
                <li><a id="selectgoods" href="?act=select&amp;comid=<?php echo $comid ?>">选择商品</a> &nbsp; </li>
                <li><a id="addself" href="?act=addself&amp;comid=<?php echo $comid ?>" onclick="alert('制做中');
                                return false;">添加自营</a> &nbsp; </li>
            </ul>
        </div>
        <?php
    }

}

$tp = new myclass();

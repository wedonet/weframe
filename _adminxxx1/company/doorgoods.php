<?php
/* 铺位管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {

        require_once(AdminApiPath . 'company/_doorgoods.php'); /* 提取数据 */

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb('<a href="goods.php?comid=' . $this->comid . '">' . $j['company']['title'] . '</a>'); //crumb加上公司名

        $this->addcrumb('设备');

        $this->addcrumb('柜门');


        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;

            case 'edit':
                $this->fname = 'formedit';
                require_once(adminpath . 'main.php');
                break;
            case 'creat':
                $this->fname = 'myform';
                require_once(adminpath . 'main.php');
                break;
            case 'sel':
                $this->fname = 'selgoods';
                require_once(adminpath . 'main.php');
                break;


            case 'esave':
            case 'nsave':
            case 'dosel': //处理选择商品
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $comid = $this->get('comid');
        $deviceid = $this->get('deviceid');
        $doorid = $this->get('doorid');

        crumb($this->crumb);

        require_once('biztab.php'); /* 商家业务选项卡 */
        ?>
        <!--位置设备信息-->
        <p></p>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="40">ID</th>
                <th>门号</th>
                <th>是否有货</th>
                <th>运行状态</th>
                <th>柜门状态</th>

                <th>商品ID</th>
                <th>店铺商品ID</th>

                <th width="150">商品名称</th>
                <th width="100">价格（元）</th>
                <th width="200">操作</th>

            </tr>

        <?php
        $basehref = '?comid=' . $comid . '&amp;deviceid=' . $deviceid . '&amp;doorid=' . $doorid;

        foreach ($list as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
            echo '<td class="hasgoods">' . $this->yesorno($v['hasgoods']) . '</td>' . PHP_EOL;
            echo '<td>' . $v['mystatus'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['doorstatus'] . '</td>' . PHP_EOL;

            echo '<td>' . $v['goodsid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['comgoodsid'] . '</td>' . PHP_EOL;

            echo '<td class="goodstitle">' . $v['goodstitle'] . (1==$v['isgroup']?'(组合)':''). '</td>' . PHP_EOL;
            echo '<td class="price">' . $v['price'] / 100 . '</td>' . PHP_EOL;

            echo '<td>' . PHP_EOL;
            echo '	<a href="?act=sel&amp;comid=' . $comid . '&amp;deviceid=' . $v['deviceid'] . '&amp;doorid=' . $v['id'] . '&amp;doornum=' . $v['title'] . '">选择售卖品</a> &nbsp; ' . PHP_EOL;




            echo '	<a href="?act=del&amp;comid=' . $comid . '&amp;deviceid=' . $v['deviceid'] . '&amp;doorid=' . $v['id'] . '&amp;doornum=' . $v['title'] . '" title="删除' . $v['title'] . '" class="j_delgoods">删除售卖品</a>  ' . PHP_EOL;

            echo '</td>' . PHP_EOL;

            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>

        <div class="tip1">
            <dl>
                <dt>说明</dt>
                <dd>删除售卖品将取消对原商品的绑定，并置柜门于无货状态，如此时这个门仍有货品，请补货人员到设备处执行补货。</dd>
            </dl>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {

                $('#j_device a').addClass('on');

                $('a.j_delgoods').bind('click', function () {
                    var obj = $(this);
                    if (!confirm(obj.attr('title'))) {
                        return false;
                    } else {
                        loading();

                        var url = obj.attr('href');

                        $.ajax({
                            cache: false,
                            type: 'POST',
                            url: url,
                            dataType: 'json', //返回json格式数据
                            success: function (json) {

                                /*保存成功*/
                                if ('y' == json.success)
                                {
                                    obj.closest('.j_parent').find('.hasgoods').text('No');
                                    obj.closest('.j_parent').find('.goodstitle').text('');
                                    obj.closest('.j_parent').find('.price').text('');
                                    removeloading();
                                } else { //保存失败，显示失败信息
                                    errdialog(json);
                                }

                            },
                            error: function (xhr, type, error) {
                                alert('Ajax error:' + xhr.responseText);
                            }
                        })

                    }

                    return false;
                })

            });

        </script>


        <?php
    }

    /* 添加 */

    function myform() {
        $j = & $GLOBALS['j'];

        $this->addcrumb('柜门管理');
        $comid = $this->get('comid');
        /* 添加时排序号是100 */
        $data['cls'] = '100';

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="nsave" />  
            <input type="hidden" name="comid" value="<?php echo $comid ?>" /> 


            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="60">ID</td>
                    <td width="*"><input type="text" name="id" id="id" value="<?php echo $this->ivalue($data, 'id') ?>" size="20"></td>
                </tr>
                <tr>
                    <td width="60">IC</td>
                    <td width="*"><input type="text" name="ic" id="ic" value="<?php echo $this->ivalue($data, 'ic') ?>" size="20"></td>
                </tr>

                <tr>
                    <td width="60">名称</td>
                    <td width="*"><input type="text" name="title" id="title" value="<?php echo $this->ivalue($data, 'title') ?>" size="20"></td>
                </tr>     

                <tr>
                    <td>是否有货</td>
                    <td><input type="text" name="hasgood" id="hasgood" size="3" value="<?php echo $this->ivalue($data, 'hasgood') ?>"></td>
                </tr>

                <tr>
                    <td>运行状态</td>
                    <td><input type="text" name="mystatus" id="mystatus" size="3" value="<?php echo $this->ivalue($data, 'mystatus') ?>"></td>
                </tr>

                <tr>
                    <td>柜门状态</td>
                    <td><input type="text" name="doorstatus" id="doorstatus" size="3" value="<?php echo $this->ivalue($data, 'doorstatus') ?>"></td>
                </tr>

                <tr>
                    <td>所属设备</td>
                    <td><input type="text" name="deviceic" id="deviceic" size="3" value="<?php echo $this->ivalue($data, 'deviceic') ?>"></td>
                </tr>

                <tr>
                    <td>商品ic</td>
                    <td><input type="text" name="goodic" id="goodic" size="3" value="<?php echo $this->ivalue($data, 'goodic') ?>"></td>
                </tr>

                <tr>
                    <td>商品id</td>
                    <td><input type="text" name="goodid" id="goodid" size="3" value="<?php echo $this->ivalue($data, 'goodid') ?>"></td>
                </tr>

                <tr>
                    <td>商品名称</td>
                    <td><input type="text" name="goodtitle" id="goodtitle" size="3" value="<?php echo $this->ivalue($data, 'goodtitle') ?>"></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="保存" class="submit1"></td>
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

                            mess['content'] = '<li><a href="?">二秒后自动返回柜门列表.</a></li>';
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

    /* 编辑 */

    function formedit() {
        $j = & $GLOBALS['j'];

        $this->addcrumb('编辑');
        $comid = $this->get('comid');

        $data = $j['doorgoods'];

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="esave" />      
            <input type="hidden" name="comid" value="<?php echo $comid ?>" /> 
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />


            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="60">ID</td>
                    <td width="*"><?php echo $data['id'] ?></td>
                </tr>

                <tr>
                    <td width="60">IC</td>
                    <td width="*"><?php echo $data['ic'] ?></td>
                </tr>

                <tr>
                    <td width="60">名称</td>
                    <td width="*"><?php echo $data['title'] ?></td>
                </tr>     

                <tr>
                    <td>是否有货</td>
                    <td><?php echo $this->ivalue($data, 'hasgood') ?></td>
                </tr>
                <tr>
                    <td>是否运行</td>
                    <td>
                        <select name="mystatus" id="mystatus">
                            <option value="1" selected="selected">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                </tr>


                <tr>
                    <td>柜门状态</td>
                    <td><?php echo $this->ivalue($data, 'doorstatus') ?></td>
                </tr>

                <tr>
                    <td>所属设备</td>
                    <td><?php echo $this->ivalue($data, 'deviceic') ?></td>
                </tr>

                <tr>
                    <td>商品ic</td>
                    <td><input type="text" name="goodic" id="goodic" size="20" value="<?php echo $this->ivalue($data, 'goodic') ?>"></td>
                </tr>

                <tr>
                    <td>商品id</td>
                    <td><input type="text" name="goodid" id="goodid" size="20" value="<?php echo $this->ivalue($data, 'goodid') ?>"></td>
                </tr>

                <tr>
                    <td>商品名称</td>
                    <td><input type="text" name="goodtitle" id="goodtitle" size="20" value="<?php echo $this->ivalue($data, 'goodtitle') ?>"></td>
                </tr>



                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="保存" class="submit1"></td>
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

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回柜门列表.</a></li>';

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

    /* 选择售卖品 */

    function selgoods() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $comid = $this->comid;

        $deviceid = $this->get('deviceid');

        $doorid = $this->get('doorid');

        $this->addcrumb('选择售卖品');

        crumb($this->crumb);
        ?>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="40">商品ID</th>
                <th width="80">店铺商品ID</th>
                <th width="*">名称</th>
                
                <th>属性</th>

                <th width="120">价格（元）</th>

                <th width="100">操作</th>
            </tr>

        <?php
        foreach ($list as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;

            echo '<td>' . $v['goodsid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;

            echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
            
            echo '<td>' . (1==$v['isgroup'] ? '组合':false).'</td>'.PHP_EOL;//

            echo '<td>' . $v['price'] / 100 . '</td>' . PHP_EOL;

            echo '<td>' . PHP_EOL;

            /* 删除平台自营商品 */
            echo '  <a href="?act=dosel&amp;comid=' . $comid . '&amp;deviceid=' . $deviceid . '&amp;doorid=' . $doorid . '&amp;comgoodsid=' . $v['id'] . '" title="选择' . $v['title'] . '" class="j_confirmedit">选择</a></td>' . PHP_EOL;

            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.j_confirmedit').j_confirmedit(function (json) {
                    /*设置成功刷新页面*/
                    if ('y' == json.success) {

                        var rebackurl = '?comid=<?php echo $comid ?>&deviceid=<?php echo $deviceid ?>';


                        ttt = setTimeout("window.location.href='" + rebackurl + "'", 2000);

                        var mess = new Array();

                        mess['content'] = '<li><a href=' + rebackurl + '>设置成功，二秒后自动返回上一页</a></li>';


                        /*弹出对话框*/
                        opdialog(mess);
                    }
                })
            })
        </script>

        <?php
    }

}

$tp = new myclass();

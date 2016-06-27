<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');
/* 先去要数据
 * 再写模板
 */

/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        require_once(ApiPath . '/biz/finance/_takelist.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->addcrumb('<a href="?">提现</a>');
        $this->comid = $this->rid('comid');
        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            case 'take': //申请提现
                $this->fname = 'myform'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            case 'export':
                $this->doexport();
                break;
            case 'viewicantake':
                $this->fname = 'viewicantake'; //查看可提现记录
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            case 'history':
                $this->fname = 'history'; //查看可提现记录
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];
        $jsonstr = json_encode($j['search']); //将数组字符串化
//$comid = $this->get('comid');

        crumb($this->crumb);
        ?>
        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 
                <input type="hidden" name="act" id="act" value="" />
                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
                <select name="mystatus" id="mystatus">
                    <option value="all">全部</option>
                    <option value="new">待审核</option>
                    <option value="checked">通过</option>
                    <option value="unchecked">未通过</option>
                    <option value="done">已打款</option>
                </select>

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
                <input type="button" id="btnexport" value=" 导出 ">

            </form>
        </div>
        <!--将选中数据的值更新到#mystatus-->
        <script type="text/javascript">
            $(document).ready(function () {

                var jsonstr = <?php echo $jsonstr ?>;//json数据,无''
                //alert(jsonstr.mystatus);
                $('#mystatus').val(jsonstr.mystatus);

            })

        </script>

        <!--提现信息-->
        <div style="font-weight:bold">
            提款中：<?php echo $j['account']['doingtake'] / 100 ?>元 &nbsp;
            余额：<?php echo $j['account']['acanuse'] / 100 ?>元

            &nbsp;&nbsp;&nbsp;<a href="?act=take" >申请提现</a>
            &nbsp;&nbsp;&nbsp;<a href="?act=viewicantake" >查看未提现记录</a>

        </div>

        <script type="text/javascript">
            <!--
                   $(document).ready(function () {
                /*点导出，把act的值设为导出，然后提交表单*/
                $('#btnexport').on('click', function () {
                    $('#act').val('export');
                    $('#formsearch').submit();
                })

                $('#btnsearch').on('click', function () {
                    $('#act').val('');
                    return true;
                })
            })
        </script>
        <?php
        if ('y' != $j['success']) {
            $this->showerrlist($j['errmsg']); //把错误信息打印出来
        } else {

            $this->showlist($j);
        }
    }

    function showerr(&$j) {
        foreach ($j['errmsg'] as $v) {
            echo $v;
        }
    }

    function showlist(&$j) {
        ?>
        &nbsp;
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="60">ID</th>
                <th width="150">申请时间</th>
                <th width="*">申请金额(元)</th>
                <th width="*">申请人</th>

                <th width="*">状态</th>
                <th width="*">备注</th>
                <th width="*">操作</th>


            </tr>

            <?php
            if (is_array($j['list']['rs'])) {
                foreach ($j['list']['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['myvalue'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . $v['fullname'] . '</td>' . PHP_EOL;

                    echo '<td>' . $v['mystatustitle'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                    echo '<td style="display:none">' . PHP_EOL;

                    if ('new' == $v['mystatus']) {
                        echo '<a href="?act=cancel&id=' . $v['id'] . '" title="取消当前申请？" class="confirmedit">取消</a> &nbsp; ' . PHP_EOL;
                    }
                    echo ' </td>' . PHP_EOL;

                    echo '  <td><a href="?act=history&amp;pid=' . $v['id'] . '">详情</a></td>';

                    echo '</tr>' . PHP_EOL;
                }
            }
            ?>
        </table>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.confirmedit').j_confirmedit(function (json) {
                    /*操作成功刷新页面*/
                    if ('y' == json.success) {
                        document.location.reload();
                    }

                })
            })
        </script>


        <?php
        $this->pagelist($j['list']['total']);
    }

    function myform() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];
        $take = & $j['take'];

        $this->addcrumb('申请提现');
        $comid = $this->get('comid');

        crumb($this->crumb);
        ?>

        <form method="post" action="?act=nsave" id="myform">
            <textarea name="idlist" style="display: none"><?php echo $j['idlist'] ?></textarea>
            <input type="hidden" name="myvalue" value="<?php echo $take['myvalue'] ?>" />
            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="65">可提款</td>
                    <td width="*"><?php echo $take['myvalue'] / 100 ?>元 共计<?php echo $take['mycount'] ?>笔 &nbsp; </td>
                </tr>

                <tr>
                    <td>提现金额</td>
                    <td><?php echo $take['myvalue'] / 100 ?> 元<span style="color:#999"></span></td>
                </tr>     
                <tr>
                    <td>开户名</td>
                    <td><?php echo $list['a_name'] ?></td>
                </tr> 
                <tr>
                    <td>开户行</td>
                    <td><?php echo $list['a_bank'] ?></td>
                </tr>
                <tr>
                    <td>银行账号</td>
                    <td><?php echo $list['a_number'] ?></td>
                </tr>
                <tr>
                    <td>到账时间</td>
                    <td>3-10个工作日</td>
                </tr>


                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="申请提现" class="submit1"></td>
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

                            ttt = setTimeout("window.location.href='?comid=<?php echo $this->comid ?>'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动刷新提现列表.</a></li>';

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

    //申请提现
    function history() {
        $j = & $GLOBALS['j'];
        $list = & $j['list'];
        $this->addcrumb('详情');
        $comid = $this->get('comid');

        crumb($this->crumb);
        ?>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="30">ID</th>
                <th width="*">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">入款</th>
                <th width="*">出款</th>
                <th width="*">余额</th>
                <th width="*">款项类型</th> 
                <th width="*">支付方式</th>
                <th width="*">原始凭证号</th>
                <th width="*">原始凭证日期</th>
                <th width="*">操作时间</th>

                <th width="*">备注</th>
                <th width="*">其它</th>
            </tr>

            <?php
            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['duid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['dnick'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                if ('0' != $v['myvalue']) {
                    echo sprintf("%.2f", $v['myvalue'] / 100);
                } else {
                    echo '';
                }
                echo '</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;
                if ('0' != $v['myvalueout']) {
                    echo sprintf("%.2f", $v['myvalueout'] / 100);
                } else {
                    echo '';
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;

                switch ($v['myway']) {
                    case '10':
                        echo '微信';
                        break;

                    case '20':
                        echo '支付宝';
                        break;

                    case '30':
                        echo '余额';
                        break;

                    default:
                        echo '';
                        break;
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                switch ($v['formdate']) {
                    case "":
                        break;
                    default:
                        echo date('Y-m-d', $v['formdate']);
                        break;
                }
                echo '</td>' . PHP_EOL;
                echo '</td>' . PHP_EOL;

                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                if ('3010' != $v['mytype']) {
                    echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                } else {
                    echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;
                }

                echo '<td>';
                /* 显示商品名称 */
                $mygoods = $v['mygoods'];
                if ('' != $mygoods) {
                    $a = json_decode($mygoods, true);
                    foreach ($a as $w) {
                        echo $w['title'] . '*' . $w['counts'] . '<br />';
                    }
                }
                echo '</td>';

                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>

        <?php
        $this->pagelist($j['list']['total']);
        ?>

        <script type="text/javascript">

            $(document).ready(function () {


            })

        </script>

        <?php
    }

    function doexport() {
        $list = & $GLOBALS['j']['list'];

        //把错误信息提示出来，不执行下载
        $j = & $GLOBALS['j'];
        if ('y' != $j['success']) {
            showerr(); //把错误信息打印出来
        } else {
            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header("Content-Disposition: attachment;filename=finance.xls ");
            header("Content-Transfer-Encoding: binary ");
        }
        ?>
        <table border="1">
            <tr>
                <th width="60">ID</th>
                <th width="150">申请时间</th>
                <th width="*">申请金额(元)</th>
                <th width="*">申请人</th>
                <th width="*">支付宝账户</th>
                <th width="*">状态</th>
                <th width="*">备注</th>
            </tr>
            <?php
            foreach ($list['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp; </td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['myvalue'] / 100) . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['fullname'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['payaccount'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['mystatustitle'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['other'] . '&nbsp;</td>' . PHP_EOL;
                echo ' </td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            echo '</table >';
            echo '<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table>';
        }

        function viewicantake() {
            $j = & $GLOBALS['j'];
            $list = & $j['list'];
            $this->addcrumb('可提现记录');

            $comid = $this->get('comid');

            crumb($this->crumb);
            ?>  
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="30">ID</th>
                    <th width="*">操作人ID</th>
                    <th width="*">操作人</th>
                    <th width="*">入款</th>
                    <th width="*">出款</th>
                    <th width="*">余额</th>
                    <th width="*">款项类型</th> 
                    <th width="*">支付方式</th>
                    <th width="*">原始凭证号</th>
                    <th width="*">原始凭证日期</th>
                    <th width="*">操作时间</th>

                    <th width="*">备注</th>
                    <th width="*">其它</th>
                </tr>

                <?php
                foreach ($j['list']['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['duid'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['dnick'] . '</td>' . PHP_EOL;
                    echo '<td>' . PHP_EOL;
                    if ('0' != $v['myvalue']) {
                        echo sprintf("%.2f", $v['myvalue'] / 100);
                    } else {
                        echo '';
                    }
                    echo '</td>' . PHP_EOL;

                    echo '<td>' . PHP_EOL;
                    if ('0' != $v['myvalueout']) {
                        echo sprintf("%.2f", $v['myvalueout'] / 100);
                    } else {
                        echo '';
                    }
                    echo '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                    echo '<td>' . PHP_EOL;

                    switch ($v['myway']) {
                        case '10':
                            echo '微信';
                            break;

                        case '20':
                            echo '支付宝';
                            break;

                        case '30':
                            echo '余额';
                            break;

                        default:
                            echo '';
                            break;
                    }
                    echo '</td>' . PHP_EOL;
                    echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
                    echo '<td>' . PHP_EOL;
                    switch ($v['formdate']) {
                        case "":
                            break;
                        default:
                            echo date('Y-m-d', $v['formdate']);
                            break;
                    }
                    echo '</td>' . PHP_EOL;
                    echo '</td>' . PHP_EOL;

                    echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                    if ('3010' != $v['mytype']) {
                        echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                    } else {
                        echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;
                    }

                    echo '<td>';
                    /* 显示商品名称 */
                    $mygoods = $v['mygoods'];
                    if ('' != $mygoods) {
                        $a = json_decode($mygoods, true);
                        foreach ($a as $w) {
                            echo $w['title'] . '*' . $w['counts'] . '<br />';
                        }
                    }
                    echo '</td>';
                    echo '</tr>' . PHP_EOL;
                }
                ?>
            </table>

            <?php
            $this->pagelist($j['list']['total']);
        }

    }

    $tp = new myclass();
    unset($tp);
    
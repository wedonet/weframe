<?php
/* 提现审核 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* ============================== */
        /* 什么情况下必须返回json格式 */

        $jsonact = array('json'
            , 'dopass'
            , 'dopaid'
            , 'nsave'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_takelist.php'; //访问接口去
        require_once(adminpath . 'checkpower.php'); //检测权限

        $this->addcrumb('<a href="?">提现审核</a>');



        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
            case 'unpass':
                $this->formpass();
                break;
            case 'paid':
                $this->formpaid();
                break;
            case 'detail': //详情
                $this->fname = 'detail'; //主内容区
                require_once(adminpath . 'main.php'); //主模板 
                break;
            case 'exportdetail':
                $this->exportdetail();
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];

        $jsonstr = json_encode($j['search']); //将数组字符串化


        crumb($this->crumb);
        ?>

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 

                <input type="text" name="comic" value="<?php echo $j['search']['comic'] ?>" placeholder="店铺IC" />
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
        <p></p>

        <!--统计信息-->
        <div style="font-weight:bold">
            <?php
            if (isset($j['account']['new'])) {
                echo $j['account']['new']['mystatusname'] . ':';
                echo ($j['account']['new']['mysum']) / 100;
                echo '元&nbsp; ';
            }

            if (isset($j['account']['checked'])) {
                echo $j['account']['checked']['mystatusname'] . ':';
                echo ($j['account']['checked']['mysum']) / 100;
                echo '元&nbsp; ';
            }

            if (isset($j['account']['unchecked'])) {
                echo $j['account']['unchecked']['mystatusname'] . ':';
                echo ($j['account']['unchecked']['mysum']) / 100;
                echo '元&nbsp; ';
            }

            if (isset($j['account']['cancel'])) {
                echo $j['account']['cancel']['mystatusname'] . ':';
                echo ($j['account']['cancel']['mysum']) / 100;
                echo '元&nbsp; ';
            }
            ?>
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
            //-->
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
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="30">ID</th>
                <th width="*">申请时间</th>
                <th width="*">申请店铺</th>
                <th width="*">店铺电话</th>
                <th width="50">开户名</th>
                <th width="50">开户行</th>
                <th width="*">银行账号</th>
                <th width="40">申请人</th>
                <th width="80">提现金额(元)</th> 
                <th width="55">申请状态</th> 
                <th width="60">备注</th> 
                <th width="83">操作</th> 
            </tr>

            <?php
            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['telfront'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['payname'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['paybank'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['payaccount'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['fullname'] . '</td>' . PHP_EOL;

                echo '<td>' . sprintf("%.2f", $v['myvalue'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . $v['mystatusname'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['other'] . '</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;

                switch ($v['mystatus']) {
                    case'new':
                        echo '<a href="?act=dopass&amp;id=' . $v['id'] . '" title="通过" class="confirmedit">通过</a>&nbsp;' . PHP_EOL;
                        echo '<a href="?act=unpass&amp;id=' . $v['id'] . '" class=j_open>未通过</a>' . PHP_EOL;
                        break;
                    case'checked':
                        echo '<a href="?act=paid&amp;id=' . $v['id'] . '" class="j_open">已打款</a>' . PHP_EOL;
                }

                echo '<a href="?act=detail&amp;pid=' . $v['id'] . '">详情</a>';

                echo '</td>' . PHP_EOL;

                echo '</tr>' . PHP_EOL;
            }
            ?>
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
        $this->pagelist($j['list']['total']);
    }

    /* 未通过原因 */

    function formpass() {
        $j = & $GLOBALS['j'];
        ?>
        <form method="post" action="?id=<?php echo $this->get('id') ?>" id="formpass">
            <input type="hidden" name="act" value="nsave" /> 

            <table class="table1" cellspacing="0" >
                <tr><th>请输入未通过原因</th></tr>
                <tr>                 
                    <td><textarea cols="50" rows="6" name="other" id="other" size="60"></textarea> </td>    
                </tr>

                <tr> 
                    <td><input type="submit" name="submit" value=" 提 交 " class="submit1"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function () {

                $('#formpass').on('submit', function () {
                    j_repost($(this), function (json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


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

    function formpaid() {
        $j = & $GLOBALS['j'];
        ?>
        <form method="post" action="?act=dopaid&amp;id=<?php echo $this->get('id') ?>" id="myform">

            <table class="table1" cellspacing="0" >
                <tr><th colspan="2">提交打款</th></tr>
                <tr>           
                    <td>凭证号：</td>
                    <td><input type="text" name="formcode" size="20" /> </td>    
                </tr>

                <tr>           
                    <td>凭证日期：</td>
                    <td><input type="text" name="formdate" size="20" /> </td>    
                </tr>
                <tr> 
                    <td><input type="submit" name="submit" value=" 提 交 " class="submit1"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function () {

                $('#myform').on('submit', function () {
                    j_repost($(this), function (json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


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

    function detail() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $this->addcrumb('详情');



        $pid = $this->get('pid');

        crumb($this->crumb);
        ?>

        <div class="navoperate">
            <ul>
                <li><a href="?act=exportdetail&amp;pid=<?php echo $pid ?>">导出</a></li>
            </ul>
        </div>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="50">ID</th>
                <th width="*">店铺</th>
                <th width="*">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">入款</th>
                <th width="*">出款</th>
<!--                <th width="*">余额</th>-->
                <th width="*">款项类型</th> 
<!--                <th width="*">支付方式</th>-->
                <th width="*">原始凭证号</th>
<!--                <th width="*">原始凭证日期</th>-->
                <th width="*">操作时间</th>

                <th width="*">备注</th>
<!--                <th width="*">其它</th>-->
            </tr>

            <?php
            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
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
//                echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
//                echo '<td>' . PHP_EOL;
//
//                switch ($v['myway']) {
//                    case '10':
//                        echo '微信';
//                        break;
//
//                    case '20':
//                        echo '支付宝';
//                        break;
//
//                    case '30':
//                        echo '余额';
//                        break;
//
//                    default:
//                        echo '';
//                        break;
//                }
//                echo '</td>' . PHP_EOL;
                echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
//                echo '<td>' . PHP_EOL;
//                switch ($v['formdate']) {
//                    case "":
//                        break;
//                    default:
//                        echo date('Y-m-d', $v['formdate']);
//                        break;
//                }
//                echo '</td>' . PHP_EOL;
                echo '</td>' . PHP_EOL;

                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                if ('3010' != $v['mytype']) {
                    echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                } else {
                    echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;
                }
echo '</tr>' . PHP_EOL;
                echo '<tr>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
                echo '<tr>' . PHP_EOL;
                echo '<td colspan="20">' . PHP_EOL;
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

    function exportdetail() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=list.xls ");
        header("Content-Transfer-Encoding: binary ");
        ?>
        <table border="1">
            <tr>
                <th width="50">ID</th>
                <th width="*">店铺</th>
                <th width="*">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">入款</th>
                <th width="*">出款</th>
<!--                <th width="*">余额</th>-->
                <th width="*">款项类型</th> 
<!--                <th width="*">支付方式</th>-->
                <th width="*">原始凭证号</th>
<!--                <th width="*">原始凭证日期</th>-->
                <th width="*">操作时间</th>
                <th width="*">操作日期</th>

                <th width="*">备注</th>
                <th width="*">其它</th>
            </tr>
            <?php
            foreach ($j['list'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
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
//                echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
//                echo '<td>' . PHP_EOL;
//
//                switch ($v['myway']) {
//                    case '10':
//                        echo '微信';
//                        break;
//
//                    case '20':
//                        echo '支付宝';
//                        break;
//
//                    case '30':
//                        echo '余额';
//                        break;
//
//                    default:
//                        echo '';
//                        break;
//                }
//                echo '</td>' . PHP_EOL;
                echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
//                echo '<td>' . PHP_EOL;
//                switch ($v['formdate']) {
//                    case "":
//                        break;
//                    default:
//                        echo date('Y-m-d', $v['formdate']);
//                        break;
//                }
//                echo '</td>' . PHP_EOL;
                echo '</td>' . PHP_EOL;

                echo '<td>' . date('Y-m-d', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . date('H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
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


            echo '</table>';
        }

    }

    $tp = new myclass(); //调用类的实例
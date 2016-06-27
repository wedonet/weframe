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
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_commoney.php'; //访问接口去
        require_once(adminpath . 'checkpower.php'); //检测权限
//      $this->comid = $this->rid('comid');
        $this->addcrumb('<a href="?">店铺财务</a>');



        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
            case 'export':
                $this->exportlist();
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];
        //print_r($list);die;
        $jsonstr = json_encode($j['search']); //将数组字符串化
//        $jsss=  json_encode( $j['list']);
        crumb($this->crumb);
//         $comid = & $this->comid;
//           require_once('fintab.php'); /*商家业务选项卡*/
        ?>

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 

                <input type="text" name="comname" value="<?php echo $j['search']['comname'] ?>" placeholder="店铺名称" />
                <input type="hidden" name="act" id="act" value="" />
                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
                <select name="paymentstatus" id="paymentstatus">
                    <option value="">全部</option>
                    <option value="0">未结款</option>
                    <option value="1">对账中</option>
                    <option value="3">已打款</option>                   
                </select>
                <select name="myfromname" id="myfromname">
                    <option value="">全部</option>
                    <option value="shendeng">神灯订单</option>
                    <option value="diannei">店内有售</option>

                </select>
                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 


            </form>
        </div>


        <!--将选中数据的值更新到#mystatus-->
        <script type="text/javascript">
            $(document).ready(function () {
                var jsonstr = <?php echo $jsonstr ?>;//json数据,无''
               
                $('#paymentstatus').val(jsonstr.paymentstatus);
              
                  $('#myfromname').val(jsonstr.myfrom);
           

            })

        </script>
        <p></p>

        <!--统计信息-->
        <div style="font-weight:bold; height: 30px;">
            <?php
            if (isset($j['account'][0])) {
                echo '未结款:';
                echo ($j['account'][0]) / 100;
                echo '元&nbsp; ';
            }

            if (isset($j['account'][1])) {
                echo '对账中:';
                echo ($j['account'][1]) / 100;
                echo '元&nbsp; ';
            }

            if (isset($j['account'][3])) {
                echo '已结款:';
                echo ($j['account'][3]) / 100;
                echo '元&nbsp; ';
            }

            $href = '?act=export';

            if ('' != $j['search']['comname']) {
                $href .= '&comname=' . $j['search']['comname'];
            }
            if ('' != $j['search']['date1']) {
                $href .= '&date1=' . $j['search']['date1'];
            }
            if ('' != $j['search']['date2']) {
                $href .= '&date2=' . $j['search']['date2'];
            }
            if ('' != $j['search']['paymentstatus']) {
                $href .= '&paymentstatus=' . $j['search']['paymentstatus'];
            }

            if (!array_key_exists('errmsg', $j)) {
                echo '<a href="' . htmlencode($href) . '" class="btnexport">导出</a>';
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
                <th width="50">ID</th>
                <th width="*">店铺</th>
                <th width="50">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">入款</th>
                <th width="*">出款</th>
                <th width="*">余额</th>
                <th width="50">款项类型</th> 
        <!--                <th width="*">支付方式</th>-->
                <th width="80">凭证号</th>
                <th width="*">订单类型</th>
                <th width="60">操作时间</th>

                <th width="60">备注</th>
                <th width="*">状态</th>

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
                echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '</td>' . PHP_EOL;
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
                echo '<td>' . $v['myfromname'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                if ('3010' != $v['mytype']) {
                    echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                } else {
                    echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;
                }

                echo '<td>' . $this->formatpaymentstatus($v['paymentstatus']) . '</td>';
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

    function exportlist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=finance.xls ");
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
                <th width="*">余额</th>
                <th width="*">款项类型</th> 
        <!--                <th width="*">支付方式</th>-->
                <th width="80">凭证号</th>
                <th width="*">订单类型</th>
                <th width="*">操作日期</th>
                <th width="*">操作时间</th>

                <th width="*">备注</th>
                <th width="*">状态</th>
                <th width="80">其它</th>
            </tr>
            <?php
            $myvalue = 0;
            $myvalueout = 0;


            foreach ($list as $v) {

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
                echo '&nbsp;</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;
                if ('0' != $v['myvalueout']) {
                    echo sprintf("%.2f", $v['myvalueout'] / 100);
                } else {
                    echo '';
                }
                echo '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '&nbsp;</td>' . PHP_EOL;
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
                echo '<td>' . $v['formcode'] . '&nbsp;</td>' . PHP_EOL;
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
                echo '<td>' . $v['myfromname'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . date('H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                if ('3010' != $v['mytype']) {
                    echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                } else {
                    echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;
                }

                echo '<td>' . $this->formatpaymentstatus($v['paymentstatus']) . '</td>';

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
            echo '<tr>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>' . '&nbsp;</td>' . PHP_EOL;
            echo '<td>' . '&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '</tr> ' . PHP_EOL;

            echo '</table>';
        }

        /* 显示成文字 */

        function formatpaymentstatus($paymentstatus) {
            switch ($paymentstatus) {
                case 0:
                    return '未结款';
                    break;
                case 1:
                    return '对账中';
                    break;
                case 2:
                    return '已冻结';
                    break;
                case 3:
                    return '已打款';
                    break;
            }
        }

    }

    $tp = new myclass(); //调用类的实例
    unset($tp);
    
<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');


/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        require_once(ApiPath . '/biz/finance/_list.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->addcrumb('财务记录');
        $this->comid = $this->rid('comid');
        $this->act = $this->ract();
        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;

            case 'export':
                $this->doexport();
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];
        $list = & $j['list'];
        $jsonarr=  json_encode($j['search']);
     
        $comid = $this->get('comid');
        crumb($this->crumb);
        ?>

        <div class="listfilter">   
            <form id="formsearch" style="display:inline" action="?" method="get">
                <input type="hidden" name="act" id="act" value="" />
                &nbsp; 

                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
                  <select name="myfromname" id="myfromname">
                    <option value="">全部</option>
                    <option value="shendeng">神灯订单</option>
                    <option value="diannei">店内有售</option>

                </select>
                <input type="submit" value=" 搜索 ">
                <input type="button" id="btnexport" value=" 导出 ">

            </form>
        </div>


        <p></p>

        <!--财务信息-->
        <div style="font-weight:bold">           
            出款：<?php echo $j['account']['myvalueout'] / 100 ?>元 &nbsp;
            入款：<?php echo $j['account']['myvalue'] / 100 ?>元 &nbsp;
            交易：<?php echo $j['account']['mycount'] / 1 ?>笔 &nbsp;
        </div>

        <script type="text/javascript">
        <!--
            $(document).ready(function () {
                   var jsonarr=<?php echo $jsonarr ?>                          
                $("#myfromname").val(jsonarr.myfrom);
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

    function showlist(&$j) {
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
                    <th width="*">订单类型</th>
                <th width="*">操作时间</th>

                <th width="*">备注</th>
                <th width="60">状态</th>
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
                   echo '<td>' . $v['myfromname'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
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
            ?>
        </table>

        <?php
        $this->pagelist($j['list']['total']);
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
                <th width="*">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">入款</th>
                <th width="*">出款</th>
                <th width="*">余额</th>
                <th width="*">款项类型</th> 
                    <th width="*">订单类型</th>
                <th width="*">操作时间</th>

                <th width="*">备注</th>
                <th width="60">状态</th>
                <th width="*">其它</th>
            </tr>
            <?php
            $myvalue = 0;
            $myvalueout = 0;


            foreach ($list as $v) {

                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['duid'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['dnick'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                if ('0' != $v['myvalue']) {
                    echo sprintf("%.2f", $v['myvalue'] / 100);
                    $myvalue+=sprintf("%.2f", $v['myvalue'] / 100);
                } else {
                    echo '';
                }
                echo '&nbsp;</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;
                if ('0' != $v['myvalueout']) {
                    echo sprintf("%.2f", $v['myvalueout'] / 100);
                    $myvalueout+=sprintf("%.2f", $v['myvalueout'] / 100);
                } else {
                    echo '';
                }
                echo '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['mytypename'] . '&nbsp;</td>' . PHP_EOL;
                   echo '<td>' . $v['myfromname'] . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                if ('3010' != $v['mytype']) {
                    echo '<td>' . $v['other'] . '&nbsp;</td>' . PHP_EOL;
                } else {
                    echo '<td>定单ID:' . $v['orderid'] . '&nbsp;</td>' . PHP_EOL;
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
            echo '<td>合计</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>' . sprintf("%.2f", $myvalue) . '&nbsp;</td>' . PHP_EOL;
            echo '<td>' . sprintf("%.2f", $myvalueout) . '&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '</tr> ' . PHP_EOL;
            echo'</table>';
            echo '<table><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table>';
        }

        /* 显示成文字 */

        function formatpaymentstatus($paymentstatus) {
            switch ($paymentstatus) {
                case 0:
                    return '';
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

    $tp = new myclass();
    unset($tp);
    
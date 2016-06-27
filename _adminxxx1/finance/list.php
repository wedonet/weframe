<?php
/* 财务列表 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');


require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_list.php'; //访问接口去

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        require_once(adminpath . 'checkpower.php'); //检测权限
        $this->addcrumb('财务列表');

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;

            case 'export':
                $this->doexport();
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];


        crumb($this->crumb);
        ?>

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 

                <input type="text" name="comic" value="<?php echo $j['search']['comic'] ?>" placeholder="店铺IC" />
                <input type="text"  name="comtitle" value="<?php echo $j['search']['comtitle'] ?>" placeholder="店铺名称"/> 
                <input type="hidden" name="act" id="act" value="" />

                <?php echo $j['search']['comname'] ?> &nbsp;  


                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
                <input type="button" id="btnexport" value=" 导出 ">
            </form>
        </div>



        <p></p>

        <!--统计信息-->
        <div style="font-weight:bold">
            入款：<?php
            if (isset($j['account']['myvalue'])) {
                echo $j['account']['myvalue'] / 100;
            } else {
                echo 0;
            }
            ?>元 &nbsp;
            出款：<?php
            if (isset($j['account']['myvalueout'])) {
                echo $j['account']['myvalueout'] / 100;
            } else {
                echo 0;
            }
            ?>元 &nbsp; 
            成交：<?php
            if (isset($j['account']['mycount'])) {
                echo $j['account']['mycount'];
            } else {
                echo 0;
            }
            ?>笔
        </div>

        <script type="text/javascript">
            <!--
            $(document).ready(function() {
                /*点导出，把act的值设为导出，然后提交表单*/
                $('#btnexport').on('click', function() {
                    $('#act').val('export');
                    $('#formsearch').submit();
                })

                $('#btnsearch').on('click', function() {
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
        if (false == $j['list']['rs']) {
            echo '<div style="color:red">当前无记录</div>';
        } else {
            ?>
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="50">ID</th>
                    <th width="*">操作人ID</th>
                    <th width="*">操作人</th>
                    <th width="*">入款(元)</th>
                    <th width="*">出款(元)</th> 

                    <th width="*">余额(元)</th>

                    <th width="*">款项类型</th> 
                    <th width="50">支付方式</th> 
                    <th width="*">原始凭证号</th> 
                    <th width="*">店铺IC</th> 
                    <th width="*">店铺名称</th> 
                    <th width="*">时间</th>
                    <th width="100">备注</th>



                </tr>

                <?php
                foreach ($j['list']['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['unick'] . '</td>' . PHP_EOL;

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

                    echo '<td>' . $v['mywayname'] . '</td>' . PHP_EOL;

                    echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['comic'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
                    echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                    echo '<td>' . $v['other'] . '</td>' . PHP_EOL;

                    echo '</tr>' . PHP_EOL;
                }
                ?>
            </table>


            <?php
            $this->pagelist($j['list']['total']);
        }
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
                <th width="50">ID</th>
                <th width="*">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">入款(元)</th>
                <th width="*">出款(元)</th> 

                <th width="*">余额(元)</th>

                <th width="*">款项类型</th> 
                <th width="*">支付方式</th> 
                <th width="*">原始凭证号</th> 
                <th width="*">店铺IC</th> 
                <th width="*">商家名称</th> 
                <th width="*">日期</th>
                <th width="*">时间</th>
                <th width="*">备注</th>
            </tr>
        <?php
        $myvalue = 0;
        $myvalueout = 0;


        foreach ($list as $v) {

            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['unick'] . '</td>' . PHP_EOL;

            echo '<td>' . sprintf("%.2f", $v['myvalue'] / 100) . '&nbsp;</td>' . PHP_EOL;
            $myvalue+=sprintf("%.2f", $v['myvalue'] / 100);

            echo '<td>' . sprintf("%.2f", $v['myvalueout'] / 100) . '&nbsp;</td>' . PHP_EOL;
            $myvalueout+=sprintf("%.2f", $v['myvalueout'] / 100);

            echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '&nbsp;</td>' . PHP_EOL;


            echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;


            echo '<td>' . $v['mywayname'] . '</td>' . PHP_EOL;

            echo '<td>' . $v['formcode'] . '&nbsp;' . '</td>' . PHP_EOL;
            echo '<td>' . $v['comic'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
            echo '<td>' . date('Y-m-d', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
            echo '<td>' . date('H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
//                echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['other'] . '&nbsp;</td>' . PHP_EOL;
            //echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;


            echo '</tr>' . PHP_EOL;
        }
        echo '<tr>' . PHP_EOL;
        echo '<td>&nbsp;</td>' . PHP_EOL;
        echo '<td>&nbsp;</td>' . PHP_EOL;
        echo '<td>&nbsp;</td>' . PHP_EOL;
        echo '<td>合计:' . $myvalue . '&nbsp;</td>' . PHP_EOL;
        echo '<td>合计:' . $myvalueout . '&nbsp;</td>' . PHP_EOL;
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

}

$tp = new myclass(); //调用类的实例
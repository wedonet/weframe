<?php
/* 日资金统计 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');


require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_accountday.php'; //访问接口去

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        require_once(adminpath . 'checkpower.php'); //检测权限
        $this->addcrumb('日资金统计');

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
            case 'esave':
            case 'nsave':
                break;
            case 'export':
                $this->doexport();
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        crumb($this->crumb);
        ?>

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                <input type="hidden" name="act" id="act" value="" />

                <input type="text" name="comic" value="<?php echo $j['search']['comic'] ?>" placeholder="店铺IC" />

                <?php echo $j['search']['comname'] ?> 					  

                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">

                <input type="radio" name="datetype" value="day" checked="checked" /> 按日统计 &nbsp; 

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
                <input type="button" id="btnexport" value=" 导出 ">
            </form>
        </div>



        <p></p>

        <!--统计信息-->
        <?php if (isset($j['account'])) { ?>
            <div style="font-weight:bold">
                入款：<?php echo $j['account']['myvalue'] / 100 ?>元 &nbsp;
                出款：<?php echo $j['account']['myvalueout'] / 100 ?>元 &nbsp; 
                成交：<?php echo $j['account']['mycount'] ?>笔
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
        }


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

                <th width="*">入款(元)</th>
                <th width="*">出款(元)</th> 
                <th width="*">时间</th>  
                <th width="*">交易量</th>
            </tr>

            <?php
            $i = 1;
            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $i . '</td>' . PHP_EOL;

                echo '<td>' . sprintf("%.2f", $v['myvalue'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['myvalueout'] / 100) . '</td>' . PHP_EOL;
//            echo '<td>' . date('y-m-d', $v['sdayint']) . '</td>' . PHP_EOL;
                echo '<td>' . $v['sdayint'] . '</td>' . PHP_EOL;

                echo '<td>' . $v['mycount'] . '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;

                $i++;
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
                <th width="50">ID</th>

                <th width="*">入款(元)</th>
                <th width="*">出款(元)</th> 
                <th width="*">时间</th>  
                <th width="*">交易量</th>
            </tr>
            <?php
            $myvalue = 0;
            $myvalueout = 0;
            $mycount=0;



          $i = 1;
            foreach ($j['list'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $i . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['myvalue'] / 100) . '&nbsp;</td>' . PHP_EOL;
                $myvalue+=sprintf("%.2f", $v['myvalue'] / 100);
                echo '<td>' . sprintf("%.2f", $v['myvalueout'] / 100) . '&nbsp;</td>' . PHP_EOL;
                $myvalueout+=sprintf("%.2f", $v['myvalueout'] / 100);
                echo '<td>' . $v['sdayint'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['mycount'] . '&nbsp;</td>' . PHP_EOL;
                $mycount+=$v['mycount'];
                echo '</tr>' . PHP_EOL;

                $i++;
            }
            echo '<tr>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>合计:' . $myvalue . '&nbsp;</td>' . PHP_EOL;
            echo '<td>合计:' . $myvalueout . '&nbsp;</td>' . PHP_EOL;
            echo '<td>&nbsp;</td>' . PHP_EOL;
            echo '<td>合计:' . $mycount . '&nbsp;</td>' . PHP_EOL;
            echo '</tr> ' . PHP_EOL;

            echo '</table>';
            echo '<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;<table><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table>';
        }

    }

    $tp = new myclass(); //调用类的实例
    
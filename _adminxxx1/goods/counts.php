<?php
/* 商品浏览统计 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        /* 访问接口 */
        require_once(AdminApiPath . 'goods/_counts.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->addcrumb('商品管理');
        $this->addcrumb('浏览统计');

        $this->act = $this->ract();

        switch ($this->act) {


            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'export':
                $this->doexport();
                break;
        }
    }

    /* 商品列表 */

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        crumb($this->crumb);
        ?>
        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 

                <input type="text"  name="comid" value="<?php echo $j['search']['comid'] ?>" placeholder="店铺ID"/> 
        <!--                alert("<?php echo $j['search']['comid'] ?>");-->
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
        ?>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">Id</th>
                <th>时间</th>	
                <th>IP地址</th>
                <th>柜门ID</th>
                <th>店铺ID</th>
                <th>位置ID</th>
                <th>设备ID</th>
                <th>平台商品ID</th>
                <th>商家商品ID</th>
                <th>用户ID</th>
                <th>价格(元)</th>
            </tr>

            <?php
            foreach ($j['list']['rs'] as $v) {
                echo '<tr>' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . $v['myip'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['doorid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['comid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['placeid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['deviceid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['goodsid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['comgoodsid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['price'] / 100) . '</td>' . PHP_EOL;
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
            header('Content-Disposition: attachment;filename="goodscounts.xls"');
            header("Content-Transfer-Encoding:binary");
        }

        echo '<table border="1">';

        echo '	<tr>';
        echo '  <th>Id</th>';
        echo '	<th>日期</th>	';
        echo '	<th>时间</th>	';
        echo '	<th>IP地址</th>';
        echo '	<th>柜门ID</th>';
        echo '	<th>商家ID</th>';
        echo '	<th>位置</th>';
        echo '	<th>设备ID</th>';
        echo '	<th>平台商品ID</th>';
        echo '	<th>商家商品ID</th>';
        echo '	<th>用户ID</th>';
        echo '	<th>价格</th>';
        echo '</tr>';

        foreach ($list['rs'] as $v) {
            echo '<tr>' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . date('Y-m-d', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
            echo '<td>' . date('H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
            echo '<td>' . $v['myip'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['doorid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['comid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['placeid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['deviceid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['goodsid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['comgoodsid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
            echo '<td>' . sprintf("%.2f", $v['price'] / 100) . '&nbsp;</td>' . PHP_EOL;
            echo '</tr>' . PHP_EOL;
        }

        echo '</table>';
    }

}

$tp = new myclass();
unset($tp);

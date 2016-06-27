<?php
/* 商品管理 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');

/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        require_once(ApiPath . '/biz/goods/_goodsaccount.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb('<a href="goods.php?comid=' . $this->comid . '">' . $j['company']['title'] . '</a>'); //crumb加上公司名

        $this->addcrumb('商品统计');




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

    /* 商家列表 */

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $comid = $this->get('comid');

        crumb($this->crumb);
        ?>
        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">

                <input type="hidden" name="comid" value="<?php echo $this->comid ?>" />
                &nbsp; 时间段：
                <input type="hidden" name="act" id="act" value="" />
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;

                <input type="submit" value=" 搜索 " id="btnsearch">
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
        </script>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">店铺商品ID</th>
                <th width="*">名称</th>
                <th width="240">价格(元)</th>
                <th width="60">成交单数</th>

            </tr>

            <?php
            if ('y' != $j['success']) {
                $this->showerrlist($j['errmsg']); //把错误信息打印出来
            }

            if (false != $list) {
                foreach ($list as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['price'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . $v['mycount'] . '</td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
            }
            ?>
        </table>

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
                <th width="80">店铺商品ID</th>
                <th width="*">名称</th>
                <th width="240">价格(元)</th>
                <th width="80">成交单数</th>
            </tr>
        <?php
        foreach ($list as $v) {            
                   echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['title'] . '&nbsp;</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['price'] / 100) . '&nbsp;</td>' . PHP_EOL;
                    echo '<td>' . $v['mycount'] . '&nbsp;</td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
               
            }
    }

}

$tp = new myclass();

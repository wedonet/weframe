<?php
/* 商品管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        /* 访问接口 */
        require_once(AdminApiPath . 'goods/_goodscount.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->addcrumb('商品管理');
        $this->addcrumb('商品统计');
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
        $countarray = & $j['countarray'];
        $salenum_array = & $j['salenum_array'];
        crumb($this->crumb);
        ?>

        <!--        <div class="navoperate">
                    <ul>
                        <li><a href="?act=creat">添加商品</a></li>
                    </ul>
                </div>-->

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 

                <input type="text" name="title" value="<?php echo $j['search']['title'] ?>" placeholder="商品名称" />
                <input type="text" name="ic" value="<?php echo $j['search']['ic'] ?>" placeholder="商品ic" />
                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
                <input type="text" name="u_nick" value="<?php echo $j['search']['u_nick'] ?>" placeholder="店铺名称" />
                <input type="hidden" name="act" id="act" value="" />

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
                <input type="button" id="btnexport" value=" 导出 ">
                <script type="text/javascript">
                    <!--
                    $(document).ready(function () {
                        /*点导出，把act的值设为导出，然后提交表单*/
                        $('#btnexport').on('click', function () {
                            $('#act').val('export');
                            $('#formsearch').submit();
                        });
                        $('#btnsearch').on('click', function() {
                    $('#act').val('');
                    return true;
                })



                    })
                    //-->
                </script>
            </form>
        </div>

        <?php
        if ('y' != $j['success']) {
            $this->showerrlist($j['errmsg']); //把错误信息打印出来
            exit();
        }
        if (false == $list['rs']) {
            echo '没有满足搜索条件的数据';
        } else {
            ?>
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="80">预览图</th>
                    <th width="40">商品ID</th>
                    <th width="*">商品IC</th>
                    <th width="200">名称</th>
                    <th width="200">浏览量</th>
                    <th width="*">平台库存</th>                
                    <th width="*">总库存</th>                
                    <th width="100">已售卖数</th>
                </tr>

                <?php
                foreach ($list['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td style="overflow:hidden"><img src="' . $v['preimg'] . '" height="40" alt="" /></td>' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                    echo '<td class="omit" title=' . $v['title'] . '>' . $v['title'] . '</td>' . PHP_EOL;
                    if (isset($countarray[$v['id']])) {
                        echo '<td>' . $countarray[$v['id']] . '</td>' . PHP_EOL;
                    } else {
                        echo '<td>0</td>' . PHP_EOL;
                    }
                    echo '<td>' . $v['inventories'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['inventoriessum'] . '</td>' . PHP_EOL;
                    if (isset($salenum_array[$v['id']])) {
                        echo '<td>' . $salenum_array[$v['id']] . '</td>' . PHP_EOL;
                    } else {
                        echo '<td>0</td>' . PHP_EOL;
                    }
                }
                ?>

            </table>
            <?php
            $this->pagelist($j['list']['total']);
        }
    }

    /* 添加商品 */

    function showerr(&$j) {
        foreach ($j['errmsg'] as $v) {
            echo $v;
        }
    }

    function doexport() {
        $j = & $GLOBALS['j'];
        $list = & $j['list'];
        $countarray = & $j['countarray'];
        $salenum_array = & $j['salenum_array'];
        if ('y' != $j['success']) {
            $this->showerrlist($j['errmsg']); //把错误信息打印出来
            exit();
        }
        
        
        
        
        
        if (false == $list['rs']) {
            echo '没有找到此商品';
        } else {
            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition: attachment;filename="goodscounts'.date("Y-m-d").'.xls"');
            header("Content-Transfer-Encoding:binary"); 
            
            
           echo '             <table border="1" cellspacing="0">';
           if(!empty($_GET['u_nick'])){
               echo ' <tr><td >店铺名称:'.  htmlspecialchars($_GET['u_nick']).'</td></tr>';
           } 
           echo '    
                <tr>                   
                    <th >商品ID</th>
                    <th >商品IC</th>
                    <th >名称</th>
                    <th >浏览量</th>
                    <th >平台库存</th>                
                    <th >总库存</th>                
                    <th >已售卖数</th>
                </tr>' ;

                
                foreach ($list['rs'] as $v) {
                    echo '<tr >' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td> ' . $v['ic']. ' </td>' . PHP_EOL;
                    echo '<td >' . $v['title'] . '</td>' . PHP_EOL;
                    if (isset($countarray[$v['id']])) {
                        echo '<td>' . $countarray[$v['id']] . '</td>' . PHP_EOL;
                    } else {
                        echo '<td>0</td>' . PHP_EOL;
                    }
                    echo '<td>' . $v['inventories'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['inventoriessum'] . '</td>' . PHP_EOL;
                    if (isset($salenum_array[$v['id']])) {
                        echo '<td>' . $salenum_array[$v['id']] . '</td>' . PHP_EOL;
                    } else {
                        echo '<td>0</td>' . PHP_EOL;
                    }
                }
                echo '</table>';               
//            $this->pagelist($j['list']['total']);
        }
    }

    function jsontostring($json) {
        if ('' == $json) {
            return;
        }

        $a = json_decode($json, true);
        $count = count($a);
        for ($i = 0; $i < $count; $i++) {
            echo $a[$i]['id'] . '*' . $a[$i]['count'];
            if ($i < ($count - 1)) {
                echo ',';
            }
        }
    }

}

$tp = new myclass();

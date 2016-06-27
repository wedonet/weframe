<?php
/* 财务列表 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        require_once (ApiPath . '/biz/business/_order.php'); //访问接口去
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb('定单管理');

        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php'); //主模板
                break;

            case 'export':
                $this->doexport();
                break;
        }
    }

    //柜门显示函数（在所有柜门里区分显示已打开和未打开的）
    function doorcolor($alllocker, $goodlocker, $badlocker) {
        $a = explode(',', $alllocker);
        $i = 0;
        $s2 = ',' . $goodlocker . ',';
        $s3 = ',' . $badlocker . ',';
        foreach ($a as $v) {

            $s1 = ',' . $v . ',';


//            echo '$s1='. $s1;
//            echo ' $goodlocker='. $s2;
//            echo ' $badlocker='. $s3;
//            echo ' strpos($s1, $s2)=' . strpos($s1, $s2);
//            echo '<br />';
//            echo '<br />';
            if (strpos($s2, $s1) !== false) {
                $a[$i] = '<span style="color:#00baff">' . $v . '</span>';
            } else if (strpos($s3, $s1) !== false) {
                $a[$i] = '<span style="color:#f00">' . $v . '</span>';
            } else {
                $a[$i] = $v;
            }


            $i++;
        }
        return join(',', $a);
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];

        $comid = $this->get('comid');
        $mystatus=$j['search']['mystatus'];

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
                
                 <select name="mystatus" id="mystatus">                    
                    <option value="">全部状态</option>
                    <?php foreach($mystatus as $ks=>$vs){
                        if(isset($_GET['mystatus']) && $_GET['mystatus']==$ks){
                            $select="selected";
                        }else{
                            $select='';
                        }
                        echo " <option value=\"$ks\" $select>$vs</option>";                        
                    } ?>

                </select>
                <input type="submit" id="btnsearch" value=" 搜索 ">
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
//                    alert($('#act').val());
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

//print_r($list);
    //  die;
    function showlist(&$j) {
        ?>
        <p></p>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="60">定单ID</th>
                <th width="80">定单价格(元)</th>      
                <th width="60">佣金(元)</th>  
                <th width="60">下单时间</th>
                <th>用户id</th>
                <!--<th>商品</th> -->               
                <th>铺位</th>
                <th>是否支付</th>
                <th>定单类型</th>
                <th>柜门状态</th>
                <th>柜门号</th>
                <th>定单状态</th>

            </tr>

            <?php
            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                // echo '<tr class="">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['allprice'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['commission'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['building'] . '栋-' . $v['floor'] . '层-' . $v['placetitle'] . '</td>' . PHP_EOL;
                echo '<td>' . $this->yesorno($v['ispayed']) . '</td>' . PHP_EOL;


                echo '<td>';
                if (0 == $v['mytype']) {
                    echo '临时';
                } else {
                    echo '正式';
                }
                echo '</td>' . PHP_EOL;
                echo '</td>' . PHP_EOL;

                echo '<td>' . $v['doorstatus'] . '</td>' . PHP_EOL;
                echo '<td style="word-wrap:break-word;word-break:break-all;">' . PHP_EOL;
                echo $this->doorcolor($v['alllocker'], $v['goodlocker'], $v['badlocker']);
                echo '</td>' . PHP_EOL;

                echo '<td>' . $v['mystatusname'] . '</td>' . PHP_EOL;
                //echo '</tr>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;

                echo '<tr>' . PHP_EOL;
                echo '<td colspan="20">' . PHP_EOL;
                $orderlist = json_decode($v['mygoods'], true);

                if(!empty($orderlist)){
                    foreach ($orderlist as $x) {
                    echo '<div>' . PHP_EOL;
                    echo '<img src="' . $x['preimg'] . '" alt="" width="60" height="45" />' . PHP_EOL;
                    if(isset($x['doortitle'])){echo '<span class="">' . $x['doortitle'] . '门</span>' . PHP_EOL;}
//                    echo '<span>' .sprintf("%.2f",  $x['price'] / 100) . '元</span>' . PHP_EOL;
                    echo '<span class="goodstitle1">' . $x['title'] . ' x ' . $x['counts'] . ' </span>' . PHP_EOL;
                    echo '</div>' . PHP_EOL;
                }
                }

                echo '</td>' . PHP_EOL;
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
                <th width="60">定单ID</th>
                <th width="80">定单价格(元)</th>      
                <th width="60">佣金(元)</th>  
                <th width="*">下单时间</th>
                <th>用户id</th>
                <!--<th>商品</th> -->               
                <th width="*">铺位</th>
                <th width="*">是否支付</th>
                <th width="*">定单类型</th>
                <th width="*">定单状态</th>
                <th width="*">商品名称</th>
            </tr>
            <?php
            if (is_array($j['list'])) {
                foreach ($j['list'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    // echo '<tr class="">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['allprice'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['commission'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . $v['stime'] . ' &nbsp;</td>' . PHP_EOL;
                    echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['building'] . '栋-' . $v['floor'] . '层-' . $v['placetitle'] . '</td>' . PHP_EOL;
                    echo '<td>' . $this->yesorno($v['ispayed']) . '</td>' . PHP_EOL;


                    echo '<td>';
                    if (0 == $v['mytype']) {
                        echo '临时';
                    } else {
                        echo '正式';
                    }
                    echo '</td>' . PHP_EOL;
                    echo '<td>' . $v['mystatusname'] . '</td>' . PHP_EOL;
                    //echo '</tr>' . PHP_EOL;
                    echo '<td >' . PHP_EOL;
                    $orderlist = json_decode($v['mygoods'], true);

                    foreach ($orderlist as $x) {
                        echo '<div>' . PHP_EOL;
                        echo '<span class="">' . $x['doortitle'] . '门</span>' . PHP_EOL;
//                    echo '<span>' .sprintf("%.2f",  $x['price'] / 100) . '元</span>' . PHP_EOL;
                        echo '<span class="goodstitle1">' . $x['title'] . ' x ' . $x['counts'] . ' </span>' . PHP_EOL;
                        echo '</div>' . PHP_EOL;
                    }

                    echo '</td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
            }
        }

    }

    $tp = new myclass(); //调用类的实例
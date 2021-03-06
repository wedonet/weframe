<?php
/* 财务列表 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

require_once(adminpath . 'public.php');


require_once AdminApiPath . 'business' . DIRECTORY_SEPARATOR . '_hotel_order.php'; //访问接口去

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        require_once(adminpath . 'checkpower.php'); //检测权限
        $this->addcrumb('定单管理');



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
        $mystatus = $j['search']['mystatus'];
//        print_r($mystatus);


        crumb($this->crumb);
        ?>

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 

                <input type="text" name="comic" value="<?php echo $j['search']['comic'] ?>" placeholder="店铺IC" />
                <input type="hidden" name="act" id="act" value="" />

                <?php echo $j['search']['comname'] ?> &nbsp;  


                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
                <select name="mystatus" id="mystatus">                    
                    <option value="">全部状态</option>
                    <?php
                    foreach ($mystatus as $ks => $vs) {
                        if (isset($_GET['mystatus']) && $_GET['mystatus'] == $ks) {
                            $select = "selected";
                        } else {
                            $select = '';
                        }
                        echo " <option value=\"$ks\" $select>$vs</option>";
                    }
                    ?>

                </select>

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
                <input type="button" id="btnexport" value=" 导出 ">
                <a style="float: right" href="order.php">E神灯订单</a>
            </form>
        </div>



        <p></p>



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

    //柜门显示函数（在所有柜门里区分显示已打开和未打开的）
    function doorcolor($alllocker, $goodlocker, $badlocker) {
        $a = explode(',', $alllocker);
        $i = 0;
        $s2 = ',' . $goodlocker . ',';
        $s3 = ',' . $badlocker . ',';
        foreach ($a as $v) {

            $s1 = ',' . $v . ',';
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

    function showlist(&$j) {
        ?>
        <table class="table1 j_list" cellspacing="0">
            <tr> 
                <th width="30">定单ID</th>
                <th width="50">定单价格(元)</th>      
                <th width="35">佣金(元)</th>  
                <th width="40">下单时间</th>
                <th width="40">店铺IC</th>
                <th width="40">店铺</th>
                <th width="35">房间</th>
                <th width="30">送单员ID</th>
                <th width="30">送单员名称</th>
                <th width="30">接单员ID</th>
                <th width="30">接单员名称</th>
                <th width="30">派送状态</th>
                <th width="30">是否支付</th> 
                <th width="30">支付方式</th>
                <th width="40">定单类型</th> 
                <th width="35">送货时间</th>
                <th width="35">接收时间</th>
                <th width="30">定单状态</th>
            </tr>
            <?php
            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['allprice'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['commission'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                echo '<td style="word-wrap:break-word;word-break:break-all;">' . $v['comic'] . '</td>' . PHP_EOL;
                echo '<td>' . $this->shop($v['comid']) . '</td>' . PHP_EOL;
                echo '<td>' . $v['door_num']. '</td>' . PHP_EOL;                

               echo '<td>' . $v['uid_send'] . '</td>' . PHP_EOL;
               echo '<td>' . $v['uname_send'] . '</td>' . PHP_EOL;
               echo '<td>' . $v['uid_accept'] . '</td>' . PHP_EOL;
               echo '<td>' . $v['uname_accept'] . '</td>' . PHP_EOL;
               if($v['type_send']==1){
                   $v['type_send_type']="接单";                
               }elseif($v['type_send']==2){
                   $v['type_send_type']="收到";
               }else{
                   $v['type_send_type']="";
               }
               echo '<td>' . $v['type_send_type'] . '</td>' . PHP_EOL;               
                
                
                echo '<td>' . $this->yesorno($v['ispayed']) . '</td>' . PHP_EOL;
                echo '<td>' . $v['mywayname'] . '</td>' . PHP_EOL;
                echo '<td>';
                if (0 == $v['mytype']) {
                    echo '临时';
                } else {
                    echo '正式';
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' ; 
                if(isset($v['time_accept'])){ echo date("Y-m-d H:i:s", $v['time_send']); }
                echo   '</td>' . PHP_EOL;
                echo '<td>' ; 
                if(isset($v['time_accept'])){ echo date("Y-m-d H:i:s", $v['time_accept']); }
                echo   '</td>' . PHP_EOL;                
                echo '<td>' . $v['mystatusname'] . '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
//                echo '<tr>' . PHP_EOL;
//                echo '</tr>' . PHP_EOL;
                echo '<tr>' . PHP_EOL;
                echo '<td colspan="20">' . PHP_EOL;
                $orderlist = json_decode($v['mygoods'], true);

                foreach ($orderlist as $x) {
                    echo '<div>' . PHP_EOL;
                    echo '<img src="' . $x['preimg'] . '" alt="" width="60" height="45" />' . PHP_EOL;
                    
//                    echo '<span>' .sprintf("%.2f",  $x['price'] / 100) . '元</span>' . PHP_EOL;
                    echo '<span class="goodstitle1">' . $x['title'] . ' x ' . $x['counts'] . ' </span>' . PHP_EOL;
                    echo '</div>' . PHP_EOL;
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
            header("Content-Disposition: attachment;filename=order.xls ");
            header("Content-Transfer-Encoding: binary ");
        }
        ?>
        <table border="1">
            <tr> 
                <th >定单ID</th>
                <th >定单价格(元)</th>      
                <th >佣金(元)</th>  
                <th >下单日期</th>
                <th >下单时间</th>
                <th >店铺IC</th>
                <th >店铺</th>
                <th >房间</th>
                <th >送单员ID</th>
                <th >送单员名称</th>
                <th >接单员ID</th>
                <th >接单员名称</th>
                <th >派送状态</th>
                <th >是否支付</th> 
                <th >支付方式</th>
                <th >定单类型</th> 
                <th >送货时间</th> 
                <th >接受时间</th> 
                <th >定单状态</th>
            </tr>
            <?php
            foreach ($j['list'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['allprice'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . sprintf("%.2f", $v['commission'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                echo '<td>' . date('H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                echo '<td style="word-wrap:break-word;word-break:break-all;">' . $v['comic'] . '</td>' . PHP_EOL;
                echo '<td>' . $this->shop($v['comid']) . '</td>' . PHP_EOL;
                echo '<td>' . $v['door_num']. '</td>' . PHP_EOL;                
                
                echo '<td>' . $v['uid_send'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['uname_send'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['uid_accept'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['uname_accept'] . '</td>' . PHP_EOL;
                if($v['type_send']==1){
                    $v['type_send_type']="接单";                
                }elseif($v['type_send']==2){
                    $v['type_send_type']="收到";
                }else{
                    $v['type_send_type']="";
                }
                echo '<td>' . $v['type_send_type'] . '</td>' . PHP_EOL;     
                
                
                echo '<td>' . $this->yesorno($v['ispayed']) . '</td>' . PHP_EOL;
                echo '<td>' . $v['mywayname'] . '</td>' . PHP_EOL;
                echo '<td>';
                if (0 == $v['mytype']) {
                    echo '临时';
                } else {
                    echo '正式';
                }
                echo '</td>' . PHP_EOL;
                                echo '<td>' ; 
                if(isset($v['time_accept'])){ echo date("Y-m-d H:i:s", $v['time_send']); }
                echo   '&nbsp;</td>' . PHP_EOL;
                echo '<td>' ; 
                if(isset($v['time_accept'])){ echo date("Y-m-d H:i:s", $v['time_accept']); }
                echo   '&nbsp;</td>' . PHP_EOL;    
                echo '<td>' . $v['mystatusname'] . '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
//                echo '<tr>' . PHP_EOL;
//                echo '</tr>' . PHP_EOL;
//                echo '<tr>' . PHP_EOL;
//                echo '<td colspan="20">' . PHP_EOL;
//                $orderlist = json_decode($v['mygoods'], true);
//
//                foreach ($orderlist as $x) {
//                    echo '<div>' . PHP_EOL;
//                    echo '<img src="' . $x['preimg'] . '" alt="" width="60" height="45" />' . PHP_EOL;
//                    echo '<span class="">' . $x['doortitle'] . '门</span>' . PHP_EOL;
////                    echo '<span>' .sprintf("%.2f",  $x['price'] / 100) . '元</span>' . PHP_EOL;
//                    echo '<span class="goodstitle1">' . $x['title'] . ' x ' . $x['counts'] . ' </span>' . PHP_EOL;
//                    echo '</div>' . PHP_EOL;
//                }
//
//                echo '</td>' . PHP_EOL;
//                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>
        <?php
    }

}

$tp = new myclass(); //调用类的实例
<?php
/* 铺位管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {

        /* 提取数据 */
        require_once(AdminApiPath . 'company/_order.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限	

        $this->comid = $this->rid('comid');
        $j = & $GLOBALS['j'];
        $this->addcrumb('<a href="goods.php?comid=' . $this->comid . '">' . $j['company']['title'] . '</a>'); //crumb加上公司名
        $this->addcrumb('定单');

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;

//            case 'edit':
//                $this->fname = 'formedit';
//                require_once(adminpath . 'main.php');
//                break;
//            case 'creat':
//                $this->fname = 'myform';
//                require_once(adminpath . 'main.php');
//                break;
//            case 'esave':
//            case 'nsave':
//                break;
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

        crumb($this->crumb);

        require_once('biztab.php'); /* 商家业务选项卡 */
        ?>         
        <p></p>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">定单ID</th>
                <th width="100">定单价格（元）</th>      
                <th width="100">佣金（元）</th>  
                <th>下单时间</th>
                <th>用户id</th>
                <th>铺位</th>
                <th>是否支付</th>
                <th>定单类型</th>
                 <th>柜门状态</th>
                <th>柜门号</th>
                <th>定单状态</th>

            </tr>

        <?php
        foreach ($list as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . sprintf("%.2f",$v['allprice'] / 100) . '</td>' . PHP_EOL;
            echo '<td>' . sprintf("%.2f",$v['commission'] / 100) . '</td>' . PHP_EOL;
            echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['building'] . '栋-' . $v['floor']. '层-' . $v['doornum'] . '</td>' . PHP_EOL;
            echo '<td>' . $this->yesorno($v['ispayed']) . '</td>' . PHP_EOL;


            echo '<td>';
            if (0 == $v['mytype']) {
                echo '临时';
            }else{ 
                echo '正式';
            }
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
//                  echo '<span>' .sprintf("%.2f",  $x['price'] / 100) . '元</span>' . PHP_EOL;
                    echo '<span class="goodstitle1">'. $x['title'] . ' x '. $x['counts'] . ' </span>' . PHP_EOL;
                    echo '</div>' . PHP_EOL;  }                    
                }


                echo '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
        
        }
        ?>
        </table>

        <?php
        $this->pagelist($j['list']['total']);
    }

}

$tp = new myclass(); //调用类的实例
<?php
/* 店铺管理 */



require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');

class myclass extends cls_template {

    function __construct() {
       require_once(ApiPath . '/biz/business/_repairhistory.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限
        $this->addcrumb('神灯报警历史记录');
  /* 没权限提示登录 */
        if (1000 == $GLOBALS['j']['errcode']) {
            
            showerr('<div class="main"><div class="take"><div class="take00" style="text-align:center;">请登录后操作，<a href="/service/login.php">点击这里登录</a></div></div></div>');
            return;
        }
        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
             require_once( syspath . '/biz/main.php' ); //主模板
                break;
          
        }
    }

    /* 设备维修列表 */

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];


        crumb($this->crumb);
        ?>
       
       <table class="table1 j_list" cellspacing="0">
            <tr>
                 <th width="*">id</th>
                <th width="*">铺位</th>
                <th width="*">故障位置</th>
                <th width="*">商品名称</th>
                <th width="*">故障时间</th>   
                  <th width="*">修复时间</th>   
                <th width="*">类型</th>
                <!--<th width="*">是否已送货</th>-->
              
            </tr>
            <?php
            foreach ($list['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                   echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['building'] . '-' . $v['floor'] . '-' . $v['title'] . '</td>' . PHP_EOL;
                echo '<td>';
                switch ($v['door']) {
                    case '0':
                        echo '整体';
                        break;
                    default:
                        echo $v['door'];
                        break;
                }
                '</td>' . PHP_EOL;
                echo '<td>' . $v['goodstitle'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
              echo '<td>' . $v['repairtime'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
//                echo '<td>';
//                switch ($v['ischange']) {
//                    case '1':
//                        echo '已送货';
//                        break;
//                    default :
//                        echo '未送货';
//                        break;
//                }
//                '</td>' . PHP_EOL;
            
                 echo '</tr>' . PHP_EOL;
            }
            ?>
            
        </table>
        <?php
        $this->pagelist($j['list']['total']);
    }

    

    

}

$tp = new myclass();
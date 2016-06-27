<?php
/* 商品管理 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');

/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 跟据act确定输出格式 */
        $jsonact = array();
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once(ApiPath . '/biz/goods/_list.php'); //访问接口

        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->addcrumb('商品列表');

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
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


        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">预览图</th>
                <th width="80">商品ID</th>
                <th width="*">名称</th>
                <th width="80">价格(元)</th> 
                <th width="80">佣金(元)</th>              
            </tr>

            <?php
            if (false != $list) {
                foreach ($list as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td style="overflow:hidden"><img src="' . $v['preimg'] . '" height="40" alt="" /></td>' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['price'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['commission'] / 100) . '</td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
            }
            ?>
        </table>
        <?php
        
    }

}

$tp = new myclass();
unset($tp);

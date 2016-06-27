<?php
/* 店铺出入库记录 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        
        /* 什么情况下必须返回json格式 */
        $jsonact = array(
            'json'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }


        /* 提取数据 */
        require_once(AdminApiPath . 'company/_store.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb('<a href="goods.php?comid=' . $this->comid . '">' . $j['company']['title'] . '</a>'); //crumb加上公司名

        $this->addcrumb('出入库记录');


        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
        }
    }

    /* 商家列表 */

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $comid = $this->get('comid');

        crumb($this->crumb);

        require_once('biztab.php'); /* 商家业务选项卡 */

        //$this->goodstab($comid); //商品管理选项卡
        ?>

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="40">ID</th>
                <th width="80">店铺商品ID</th>
                <th width="50">商品ID</th>
                <th width="*">名称</th>
                <th width="*">凭证号</th>
                
                <th width="50">数量</th>   
                

                <th width="40">类型</th>   
                <th width="120">时间</th>
            </tr>

            <?php
            foreach ($list['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>'.$v['id'].'</td>' . PHP_EOL;
                echo '<td>'.$v['comgoodsid'].'</td>' . PHP_EOL;
                echo '<td>'.$v['goodsid'].'</td>' . PHP_EOL;
                echo '<td>'.$v['title'].'</td>' . PHP_EOL;
                echo '<td>'.$v['formcode'].'</td>' . PHP_EOL;
                echo '<td>'.$v['mycount'].'</td>' . PHP_EOL;
                echo '<td>';
                switch ($v['mytype']) {
                    case 'in':
                        echo '入库';
                        break;
                    case 'sale':
                        echo '售出';
                        break;
                     case 'toplat':
                        echo '退回';
                        break;
                } '</td>' . PHP_EOL;
                echo '<td>'.$v['stime'].'</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>



        <?php
        
        $this->pagelist($list['total']);
    }

}

$tp = new myclass();
unset($tp);

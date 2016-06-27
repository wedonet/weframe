<?php
/* 店铺库存报警  */

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


        /* 访问接口 */
        require_once(AdminApiPath . 'goods/_comstorealarm.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->addcrumb('店铺库存警报');

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
        }
    }

    /* 商品列表 */

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];



        crumb($this->crumb);
        ?>
        <!--操作区css区分-->
        <div class="listfilter">
            <form id="formsearch" style="display:inline" action="?" method="get">
                <input type="text"  name="comtitle" value="<?php echo $j['search']['comtitle'] ?>" placeholder="店铺名称"/> 
                <input type="text"  name="goodstitle" value="<?php echo $j['search']['goodstitle'] ?>" placeholder="商品名称" />
                <input id="btnsearch" type="submit" value="搜索" />
            </form>
        </div>
        <?php
        if (false == $list['rs']) {
            echo '没有满足搜索条件的数据';
        } else {
            ?>

            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="80">预览图</th>
                    <th width="40">商品ID</th>
                    <th width="*">店铺</th>
                    <th width="80">店铺商品ID</th>
                    <th width="*">名称</th>
                    <th width="60">警界值</th>                
                    <th width="60">库存</th>
                </tr>

                <?php
                foreach ($list['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td><img src="' . $v['preimg'] . '" alt="" height="40" /></td>' . PHP_EOL;
                    echo '<td>' . $v['goodsid'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['title'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['inventoriesalarm'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['inventories'] . '</td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
                ?>

            </table>
            <?php
            $this->pagelist($j['list']['total']);
        }
    }

}

$tp = new myclass();
unset($tp);

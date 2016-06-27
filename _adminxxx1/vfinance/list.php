<?php
/* 储值记录 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');


require_once AdminApiPath . 'vfinance' . DIRECTORY_SEPARATOR . '_list.php'; //访问接口去

require_once( adminpath . 'checkpower.php' ); //检测权限

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        $this->addcrumb('赠款记录');

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->fname = 'pagemain'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
        }
    }

    function pagemain() {

        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];


        crumb($this->crumb);
        ?>

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 
                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 

            </form>
        </div>


        <!--        <p></p>

                统计信息
                <div style="font-weight:bold">
                    余额：<?php
        if (isset($j['account']['mytotal'])) {
            echo $j['account']['mytotal'] / 100;
        } else {
            echo 0;
        }
        ?>元 
                </div>-->

        <script type="text/javascript">
            <!--
              $(document).ready(function () {
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

    function showlist(&$j) {
        ?>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="50">ID</th>
                <th width="*">发放</th>
                <th width="*">消费</th>
                <th width="*">款项类型</th>
                
                <th width="*">会员ID</th>
                <th width="*">会员昵称</th>
                <th width="*">时间</th> 
            </tr>

            <?php
            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                if ('0' != $v['myvalue']) {
                    echo sprintf("%.2f", $v['myvalue'] / 100);
                } else {
                    echo '';
                }
                echo '</td>' . PHP_EOL;

                echo '<td>' . PHP_EOL;
                if ('0' != $v['myvalueout']) {
                    echo sprintf("%.2f", $v['myvalueout'] / 100);
                } else {
                    echo '';
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                
                echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['unick'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>

        <?php
        $this->pagelist($j['list']['total']);
    }

}

$tp = new myclass(); //调用类的实例
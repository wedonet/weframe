<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');


/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        require_once(ApiPath . '/biz/finance/_list.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->addcrumb('财务记录');
        $this->comid = $this->rid('comid');
        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];
        $list = & $j['list'];
        $comid = $this->get('comid');
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
                <input type="submit" value=" 搜索 ">

            </form>
        </div>

        <p></p>

        <!--财务信息-->
        <div style="font-weight:bold">           
            出款：<?php echo $j['account']['myvalueout'] / 100 ?>元 &nbsp;
            入款：<?php echo $j['account']['myvalue'] / 100 ?>元 &nbsp;
            交易：<?php echo $j['account']['mycount']/1 ?>笔 &nbsp;
        </div>
        &nbsp;
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
                <th width="30">ID</th>
                <th width="*">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">入款</th>
                <th width="*">出款</th>
                <th width="*">余额</th>
                <th width="*">款项类型</th> 
                <th width="*">支付方式</th>
                <th width="*">原始凭证号</th>
                <th width="*">原始凭证日期</th>
                <th width="*">操作时间</th>

                <th width="*">备注</th>
            </tr>

            <?php

            foreach ($j['list']['rs'] as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['duid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['dnick'] . '</td>' . PHP_EOL;
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
                echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '</td>' . PHP_EOL;
                echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;

                switch ($v['myway']) {
                    case '10':
                        echo '微信';
                        break;

                    case '20':
                        echo '支付宝';
                        break;

                    case '30':
                        echo '余额';
                        break;

                    default:
                        echo '';
                        break;
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                switch ($v['formdate']) {
                    case "":
                        break;
                    default:
                        echo date('Y-m-d', $v['formdate']);
                        break;
                }
                echo '</td>' . PHP_EOL;
                echo '</td>' . PHP_EOL;

                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                if ('3010'!=$v['mytype']) {
                   echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                } else {             
                 echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;
                }
                echo '</tr>' . PHP_EOL;

            }
            ?>
        </table>

        <?php
        $this->pagelist($j['list']['total']);
    }

}

$tp = new myclass();
unset($tp);
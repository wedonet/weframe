<?php
/* 储值卡统计 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        require_once AdminApiPath . 'vfinance' . DIRECTORY_SEPARATOR . '_account.php'; //访问接口去

        require_once( adminpath . 'checkpower.php' ); //检测权限
        
        $this->addcrumb('赠款统计');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'pagemain'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
        }
    }

    function pagemain() {
        $j = & $GLOBALS['j'];
        $data = $j['data'];

        crumb($this->crumb);
        ?> 

        <table class="table1" cellspacing="1" >

            <td width="150">赠款总发放：</td>
            <td><?php echo sprintf("%.2f", $j['data']['myvalue'] / 100) ?>元</td>

            <tr>
                <td>赠款总消费：</td>
                <td><?php echo sprintf("%.2f", $j['data']['myvalueout'] / 100) ?>元</td>
            </tr>

        </table>

        <?php
    }

}

$tp = new myclass();

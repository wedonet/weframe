<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

function headplus() {
    echo '<link rel="stylesheet" href="/_css/member.css" type="text/css"/>';
}

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        
        require_once(ApiPath . 'member/_order.php'); //访问接口去
        require_once('checkmember.php');//检测权限

        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区
                break;
        }
    }

    function pagemain() {

        $j = & $GLOBALS['j'];

//        $j['headtitle'] = '我的订单';
        
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">我的订单</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <div class="orders">
                <?php
                /*循环定单列表*/
                foreach($j['list']['rs'] as $v){
                    echo '<ul class="j_ul">'.PHP_EOL;
                    echo '<li class="ordertop">'.$v['comname'].'<span class="fright red">'.$v['mystatusname'].'</span></li>'.PHP_EOL;
                    
                    $orderlist = json_decode($v['mygoods'], true);
                    foreach($orderlist as $x){
                        echo '<li><img src="'.$x['preimg'].'" width="60" height="45" class="top5 fleft" /><span class="name01">'.$x['title'].'</span><span class="money01">￥ '.($x['allprice']/100).'</span></li>';
                    }
                    
                    echo '<li class="orderbottom noboder">合计：<span class="red">￥'.($v['allprice']/100).'</span><div>成交时间：'.date('Y-m-d H:i:s', $v['stimeint']).'</div></li>'.PHP_EOL;
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            <div class="emptytext" style="display:none">当前无记录！</div>
            </div>
        </div>
        <script type="text/javascript">//当ul为空时显示无记录提醒
            if($(".j_ul li").length<1){
                $('.emptytext').show();
            }
            </script>

        <?php
        
        $this->pagelist($this->j['list']['total']);
        
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>
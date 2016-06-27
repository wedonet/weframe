<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {

        require_once(ApiPath . 'bying/_content.php'); //访问接口去

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->index(); //主内容区

                break;
        }
    }

    function index() {
        $j = & $GLOBALS['j'];

        $j['headtitle'] = '神灯';

        $doorid = $this->get('doorid');

        require_once(syspath . '_public/header.php');


        /* 立即支付的链接地址 */
        $hrefpay = 'order.php?doorid=' . $doorid;
        ?>
        <div class="main">      
            <div class="title">商品详情</div> 
            <a class="titlebg" href="javascript:history.go(-1)"></a>
            <div class="con_box">
                <?php echo $j['detail']['content'] ?>
            </div>
        <!--<script type="text/javascript">
            $(".scrollLoading").scrollLoading(); //图片列表懒加载
        </script>-->
        </div>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>
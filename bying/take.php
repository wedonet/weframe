<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {

        require_once(ApiPath . 'bying/_take.php'); //访问接口去

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->index(); //主内容区
                break;
            //case 'getorder':
            //	$this->getorder(); //提取定单信息
            //	break;
        }
    }

    function index() {
        sleep(2); //等待notify处理

        $j = & $GLOBALS['j'];
        $j['headtitle'] = '神灯';

        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">提货</div>
        <div class="main">
            <div class="take">
                <?php
                $order = $j['order'];

                if (false == $order) {
                    showerr('没找到相应定单');
                }

                switch ($order['mystatus']) {
                    case 'new':
                        echo '<div class="take00">这个定单还没完成支付，如果已付款，请稍后刷新页面</div><div class="refresh"><input type="button" value="点击这里刷新" onclick="history.go(0)"></div>';
                        break;
                    case 'payed':
                        echo '<div class="take01">定单已支付，正在处理中, 请稍后刷新页面</div>';
                        break;
                    case 'taken':
                        $this->showtaken($order);
                        //echo $order['doorids'].'柜门已打开，请取走您的货品';
                        //echo '<div class="take01">柜门已打开，请取走您的货品'; //准备转换成实际门号
                        break;
                    case 'cancel':
                        echo '<div class="take00">您手慢了，商品已经被其它人买走了。支付款已退至您的神灯账户余额</div>';
                        break;
                }

                /* 这里的定单状态不能是new了，如果总是new then提示联系酒店前台 */
                ?>



            </div>  

        </div>
        <!--
                <script>
                    reload(function() {
                        window.location.reload();
                    })
                </script>-->

        <?php
        require_once(syspath . '_public/footer.php');
    }

    /* 显示取货信息，并自动跳转回来时网址 */

    function showtaken(&$order) {
        $j = & $GLOBALS['j'];
        $doorids = $order['doorids'];
        $sql = 'select title from `' . sh . '_door` where id in (' . $doorids . ')';
        $res = $GLOBALS['pdo']->fetchAll($sql);
        $titlearr = array_column($res, 'title');
        $titlestr = implode(',', $titlearr);
        $a = explode(',', $doorids);
        $doorid = $a[0];
        $orderid = $order['id'];
        ?>

        <div class="take01">
            <div class='suc'>恭喜您，交易成功！</div>

            <div class="door">
                <span>已为您打开柜门：<?php echo $titlestr ?></span>
            </div>
            <div>
                如柜门未打开请点击<a href="?act=reopen&amp;outtype=json&amp;orderid=<?php echo $orderid ?>" id="reopen" class="reopen">重新开门</a>
            </div>


            <div class="but02"><a href="index.php?d=<?php echo $doorid ?>&clear=clear&orderid=<?php echo $orderid ?>">继续浏览其他商品</a></div>
            <div class="comtel">
                <span>如有疑问请拨打&nbsp;<a href="tel:<?php echo $j['company']['telfront'] ?>"><?php echo $j['company']['telfront'] ?></a></span>

            </div>
        </div>
        <script>
            $(document).ready(function () {

                $("#reopen").bind('click', function () {
                    //$('.but02 a').attr('href', '');//开门中时，取消返回首页的跳转
                    $('.but02 a').on('click', function () {//开门中时，给a绑定click事件，做出提醒并禁止跳转到首页
                        alert('开门中……请稍后操作！');
                        return false;
                    })
                    var href = $(this).attr('href');
                    $(this).text('开门中···');//点击之后按钮字变'开门中'
                    var obj = $(this);
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: href,
                        dataType: 'json',
                        success: function (json) {
                            obj.text('重新开门');
                            $('.but02 a').unbind('click');//解除a绑定的click事件
                            if ('n' == json.success) {

                                errdialog(json);//显示错误信息  
                              
                                if (json['errmsg'].indexOf('正在打开柜门，请稍后操作') == -1)
                                {
                                    $('#bg').bind("click");

                                } else
                                {
                                    ttt = setTimeout("autoclose()", 5000);
                                    $('#bg').unbind("click");

                                }
                            } else {

                            }
                        },
                        error: function (xhr, type, error) {
                            alert('Ajax error:' + xhr.responseText);
                        }
                    })
                    return false;
                })
            })
            //ttt = setTimeout("window.location.href='index.php?d=<?php echo $doorid ?>&clear=clear&orderid=<?php echo $orderid ?>'", 2000);
        </script>

        <?php
    }

}

$tp = new myclass();
unset($tp);
?>
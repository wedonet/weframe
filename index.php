<?php
require_once(__DIR__ . '/global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();


        /* ============================== */
        /* 什么情况下必须返回json格式 */

        $jsonact = array('add'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式                      
        }

        require_once(ApiPath . '_index.php'); //访问接口去

        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区
                break;
        }
    }

    function pagemain() {
        $j = & $GLOBALS['j'];
        $company = & $j['company'];

        $j['headtitle'] = '神灯';

        require_once(syspath . '_public/header.php');;
        
        ?>

        <div class="title"><?php echo $company['title'] ?></div>
        <div class="main mynews">
            <!--消息区域-->
            <?php
//            if (isset($GLOBALS['j']['message'])) {
//                echo '<div class="news" style="display:block;">' . $GLOBALS['j']['message'] . '<a href="javascript:void(0);"><span></span></a></div>';
//            }
            ?>
            
            <div class="listview0">
                <ul class="listview01 j_empty">
                    <?php
                    foreach ($j['list'] as $v) {
                        if (1 == $v['hasgoods'] AND 'running' == $v['mystatus']) {
                            ?>

                            <li>
                                <div class="block01">
                                    <a href="../bying/index.php?deviceid=<?php echo $v['deviceid'] ?>&comid=<?php echo $v['comid'] ?>&doorid=<?php echo $v['doorid'] ?>">
                                        <div class="pic01">
                                            <img src="../_images/nopic.jpg" data-url="<?php echo $v['preimg'] ?>" alt=""  class="scrollLoading" />
                                            <div class="price">￥<span class="bold"><?php echo $v['price'] / 100 ?></span></div>
                                            <div class="blackbg"></div>
                                        </div>
                                    </a>
                                    <div class="name01"><?php echo $v['goodstitle'] ?></div>                                   
                                </div>
                            </li>

                            <?php
                        }
                    }
                    ?>
                </ul>
                <div class="clearfix"></div>
                <div class="emptytext02" style="display:none">此神灯所有商品均已售完！</div>
            </div>
        </div>


        <!--底部主导航-->
        <?php
//        define('currenttab', 'bying');
//        require_once(syspath . '_public/tab.php');
        ?>

        <script type="text/javascript">
            $(document).ready(function () {
        //如过li小于1，显示购物车无货提示
                if ($(".j_empty").has("li").length < 1) {
                    $(".emptytext02").show()
                }
            })
        </script>
<!--        <script type="text/javascript">
            $(document).ready(function () {
        //                $(".nav1 span").addClass("on");
                $(".news span").click(function () {
                    $(".news").css({display: "none"});//消息区
                });
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function () {
                /*如果商品卖没了，取消立即支付的链接 */
                var hasgoods = $('#paylink').attr('rel');
                if (0 == hasgoods) {
        //                    $('#paylink').attr('href', 'javascript:void(0)');
                    $('#bnt div').addClass('disclick');//hasgood为0时购买不可点击状态
                    $('#paylink').addClass('unlink');//hasgood为0时购物车不可点击状态

                    $('#paylink').click(function () {
                        alert("主人！该商品已售完，请选择其他商品！");
                        return false;
                    });

                    $('#bnt div').click(function () {
                        alert("主人！该商品已售完，请选择其他商品！");//此click要写在#bnt div上，#bnt上不生效！
                        return false;
                    });

                    $('.mask').show();//商品已售完图片显示
                }

                $(".scrollLoading").scrollLoading(); //图片列表懒加载

        ////注释原因：经分析，为0时也显示，能让用户知道购物车内还没有东西！
        //                if ($('#carnums').text() * 1 > 0) {
        //                    $('#carnums').show();//购物车数量大于0时显示出来
        //                }


                if (doorids.indexOf(doorid) < 0) {
                    $('#bnt').click(function () {
                        var url = $(this).attr('href');

                        $.ajax({
                            cache: false,
                            type: 'POST',
                            url: url,
                            dataType: 'json', //返回json格式数据
                            success: function (json) {
                                if ('y' == json.success) {
                                    $("#carnums").html(parseInt($("#carnums").html()) + 1);
        //                                $('#carnums').show();//购物车数量显示//////为0时也都显示了，故注释！
                                    $('#bnt div').addClass('disclick').bind('click', function () {
                                        alert("主人！该商品已经在购物车里了！");
                                        return false;
                                    })
                                } else {
                                    errdialog(json);
                                }
                            },
                            error: function (xhr, type, error) {
                                alert('Ajax error:' + xhr.responseText);
                            }
                        })
                        /*结束ajax提取*/
                        return false;
                    })

                } else {
                    $('#bnt div').addClass('disclick').bind('click', function () {
                        alert("主人！该商品已经在购物车里了！");
                        return false;
                    })
                }

        

            })


            //首页加入购物车动画
            $(function (removecar) {
                var tmp;
                $('#bnt').bind('click', function () {
                    if (tmp)
                        tmp.remove();
                    var box = $(this).parent();
                    var img = box.find("#bigimg");
                    tmp = img.clone();
                    var p = $("#bigimg").offset();//偏移位置
                    var p2 = $("#car").offset();
                    tmp.addClass('_box').css(p).appendTo(box);
                    p2 = $.extend(p2, {height: 20, width: 20, opacity: 10});
                    $(tmp).animate(p2, "slow", function () {
                        tmp.remove();

                    });
                });
            });
            //首页加入购物车动画  


        </script>-->

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>


<!--ajax有两个功能要实现：1. 点购物车，把商品id传给后台 2.重新局部加载购物车数量（数量这向后台发起请求）-->
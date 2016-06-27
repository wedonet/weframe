<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {

        require_once(ApiPath . 'bying/_cart.php'); //访问接口去

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->index(); //主内容区

                break;
        }
    }

    function index() {

        $j = & $GLOBALS['j'];


        $j['headtitle'] = '购物车';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">购物车</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <div class="checked clearfix">
                <div class="cartop j_empty">
                    <div class="checkbox_con">
                        <span>
                            <input type="checkbox" class="checkbox" id="chkall" checked="checked" title="全选" />
                            <label class="checkall" for="chkall"></label>
                            <div class="allchecks">全选</div>
                        </span>

                    </div>
                </div>
                <div class="emptytext" style="display:none">购物车内暂无商品！</div>

                <div class="carlist j_empty">
                    <form method="post" action="order.php?act=batch" id="myform" >
                        <ul>
                            <?php
                            foreach ($j['list'] as $v) {
                                ?>

                                <li class="carli">                            
                                    <div class="check">
                                        <div class="checkbox_con">
                                            <span>
                                                <input name="doorid[]" class="checkbox" type="checkbox"  id="<?php echo $v['doorid'] ?>" value='<?php echo $v['doorid'] ?>' checked="checked" />

                                                <label for="<?php echo $v['doorid'] ?>"></label>

                                            </span>
                                        </div>
                                    </div>
                                    <div class="pic08">
                                        <img src="<?php echo $v['preimg'] ?>" alt=""/>
                                    </div>
                                    <div class="cartxt">
                                        <div class="txtnolong title1"><a href="content.php?doorid=<?php echo $v['doorid'] ?>"><?php echo $v['title'] ?></a></div>
                                        <span class="bold num1"><?php echo $v['price'] / 100 ?></span>元
                                    </div>

                                </li>    

                                <?php
                            }
                            ?>

                        </ul>
                    </form>
                </div>



                <div class="but01 paybnt">合计：<span class="font24"><span id="carprice"><?php echo $j['cart']['allprice'] / 100 ?></span>元</span></div>
                <a href="javascript:void(0)" id='paygo'><div class="but01 paygo">去结算<span class="font12">(<span id="carcount"><?php echo $j['cart']['counts'] ?></span>件)</span></div></a>

                <div class="clearfix"></div>

            </div>

            <div class="downtip" style="display:none; bottom:45px;">设备掉线，暂无法进行购买，请联系前台！<br/><a href="tel:<?php echo $j['company']['telfront'] ?>"><?php echo $j['company']['telfront'] ?></a></div>
        
        </div>
    


        <script type="text/javascript">
            $(document).ready(function () {

                var mystatus = '<?php echo $j['devicemystatus'] ?>'; //当前设备的状态，判断掉线与否,断线时做出提示，并禁止继续操作
                              
                if ('n' == mystatus) {

                $(".downtip").show();
                $("#paygo").unlink();
                }
                
                var dooridfst = '<?php echo $j['dooridfst'] ?>';
                var carhref = 'index.php?d=' + dooridfst;   //给购物车列表页返回href重新赋值，跳转到购物车列表的第一个商品首页去
                if ('0' !== dooridfst) {
                    document.getElementById("carback").href = carhref;//购物车里有商品时，才执行，无商品时，执行返回上一页的操作！
                }


                var a = '<?php echo $j['cart']['counts'] ?>';//购物车数量小于1，全选及列表区域隐藏，显示文字提醒emptytext
                if (a < 1) {
                    $(".j_empty").hide();
                    $(".emptytext").show();
                }
                //商品列表为0时，全选和list不显示，加购物车为空提醒！

                $("#myform").bind('submit', function () {
                    j_post($(this), function (json) {

                        /*保存成功，跳转到支付页*/
                        if ('y' == json.success)
                        {
                            var href = '../pay/paytype.php?orderid=' + json.orderid;
                            ttt = setTimeout(window.location.href = href, 1000);
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })

                    return false;
                });
                $("#paygo").click(function () {
                    $("#myform").submit()
                })






                /*全选，返选*/
                $('#chkall').on('click', function () {
                    if (!this.checked) {
                        inverseCkb('doorid[]');
                    } else {
                        $(':input[name="doorid[]"]').each(function () {
                            this.checked = true;
                        })
                    }

                    recount();
                })

                /*点子项*/
                $(':input[name="doorid[]"]').on('click', function () {
                    if (!this.checked) {
                        $('#chkall').removeAttr('checked');
                    }

                    /*判断如果都选上了，就把全选按钮也选上*/
                    //alert($(':input[name="doorid[]"]').not("input:checked").length);
                    if ($(':input[name="doorid[]"]').not("input:checked").length < 1) {
                        $('#chkall')[0].checked = true;
                    }

                    recount();
                })

                /*重新计算价格和数量并做更新*/
                function recount() {
                    var a = [];
                    $(':input[name="doorid[]"]').each(function () {
                        if (this.checked) {
                            a.push($(this).val());//每发现一个doorid就想数组里push进一个值
                        }
                    })

                    if (a.length > 0) {
                        $.getJSON('order.php?act=getprice&dooridlist=' + a.join(','), function (json) {//把doorid拼成字符串，以get方式发给后台，后台返回json数据
                            $('#carprice').text(json.car.carprice);
                            $('#carcount').text(json.car.carcount);//更新carprice、carcount值
                        });
                    } else {
                        $('#carprice').text(0);
                        $('#carcount').text(0);
                    }


                }


                /**
                 * 反选
                 * 
                 * items 复选框的name
                 */
                function inverseCkb(items) {
                    $(':input[name="' + items + '"]').each(function () {
                        //此处用jq写法颇显啰嗦。体现不出JQ飘逸的感觉。
                        //$(this).attr("checked", !$(this).attr("checked"));

                        //直接使用js原生代码，简单实用
                        this.checked = !this.checked;
                    });
                }
            })
        </script>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>
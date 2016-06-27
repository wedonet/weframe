<?php
/* 余额支付 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 跟据act确定输出格式 */
        $jsonact = array('dopay');
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once(ApiPath . 'webpay/mymoney/_mymoneypay.php'); //访问接口去
        //require_once('checkpower.php'); //检测权限


        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区
                break;
        }
    }

    function pagemain() {
        $j = & $GLOBALS['j'];
        $j['headtitle'] = '神灯';
         require_once(syspath . '_public/header.php');
         
        /* 没权限提示登录 */
        if (1000 == $GLOBALS['j']['errcode']) {
            
            showerr('<div class="main"><div class="take"><div class="take00" style="text-align:center;">请登录后操作，<a href="/service/login.php">点击这里登录</a></div></div></div>');
            return;
        }

        $orderid = $this->rqid('orderid');

        if( 'n' == $j['success']){
            showerr();
        }

        /* 如果没权限则提示登录 */

        
        ?>
        <!--传递给js的数据-->
        <div id="data_orderid" style="display:none"><?php echo $orderid ?></div>

        <div class="title">余额支付</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>
        <div class="main">
            <div class="con_box">
                <div class="rental">总价：<span class="red"><?php echo sprintf("%.2f", $j['allprice'] / 100) ?></span>元</div>
                <div class="rental">余额：<?php echo sprintf("%.2f", $j['acanuse'] / 100) ?>元</div>

                <div class="but02"><a href="?act=dopay&amp;orderid=<?php echo $orderid ?>" class="j_do">确认支付</a></div>
            </div>
            
            <div class="downtip" id="downtip" style="display:none ; bottom:96px;" >设备掉线，暂无法进行购买，请联系前台！<br/><a href="tel:<?php echo $j['company']['telfront'] ?>"><?php echo $j['company']['telfront'] ?></a></div>
            
        </div>

        <script type="text/javascript">
            <!--
            $(document).ready(function () {
                
               var mystatus = '<?php echo $j['devicemystatus'] ?>';//当前设备的状态，判断掉线与否,断线时做出提示，并禁止继续操作
              
               if('n'==mystatus){
                   $("#downtip").show();
                   $(".but02 a").removeAttr("href").removeClass('j_do');
               }
               
                
                $('.j_do').j_do(function (json) {
                    /*保存成功*/
                    if ('y' == json.success)
                    {
                        var takeurl = '/bying/take.php?orderid=' + $('#data_orderid').text();
                        var mess = new Array();
                        mess['content'] = '<li><a href="' + takeurl + '">保存成功,二秒后转入提货页</a>.</li>';
                        opdialog(mess);

                        ttt = setTimeout(function () {
                            /*跳转到提货页*/
                            window.location.href = takeurl;
                        }, 500);

                    } else { //保存失败，显示失败信息  
                        errdialog(json);
                    }
                })
            })
            //-->
        </script>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);

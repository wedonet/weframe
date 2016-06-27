<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

function headplus() {
    echo '<link rel="stylesheet" href="/_css/member.css" type="text/css"/>';
}

class myclass extends cls_template {

    function __construct() {
        parent::__construct(); //执行上级的构造函数
        /* 跟据act确定输出格式 */
        $jsonact = array();
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once(ApiPath . 'member/_index.php'); //访问接口去
        require_once('checkmember.php'); //检测权限



        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区
                break;
        }
    }

    function pagemain() {

        $j = & $GLOBALS['j'];

//        $j['headtitle'] = '个人中心';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">个人中心</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <a href="uinfor.php"><div class="myinfor">
                    <span class="headimg"><img src="../_images/myheadimg.png" width="40" /></span>
                    <div class="infortxt"><div class="myname"><?php echo $j['user']['u_nick'] ?></div>
                        <div class="myid">会员ID：<?php echo $j['user']['id'] ?></div></div>
                </div></a>
            <div class="myul">
                <ul>
                    <li><a href="order.php" class="myorder">我的订单<span class="more1"></span></a></li>
                    <li class="noboder"><a href="account.php" class="mymoney">我的存款<span class="more1"></span></a></li>
                </ul>
                <ul>
                    <li><a href="vfinance/account.php" class="youpay">我的赠款<span class="more1"></span></a></li>
                    <li class="noboder"><a href="changepass.php" class="safety">账户安全<span class="more1"></span></a></li>
                </ul>
                <ul>
                    <a href="/service/login.php?act=loginout" class="j_do" title="是否退出"><div class="exit">退&nbsp;&nbsp;出</div></a>
                </ul>
            </div>

        </div>

        <script type="text/javascript">
            <!--
                $(document).ready(function () {
                $('a.j_do').j_do(function (json) {
                    if ('y' == json.success)
                    {
                        
                        ttt = setTimeout("window.location.href='/service/login.php'", 2000);
//                        var mess = new Array();
//                        mess['content'] = '<li><a href="/service/login.php">二秒后转登陆页面.</a></li>';
                        /*弹出对话框*/
                        opdialog(mess);
                    } else { //保存失败，显示失败信息
                        errdialog(json);
                    }
                })
                return false;
            })
            //-->
        </script>

        <!--底部主导航-->
        <?php
        define('currenttab', 'mine');
        require_once(syspath . '_public/tab.php');

        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);

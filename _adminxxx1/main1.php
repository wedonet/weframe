<?php
$data = & $GLOBALS['j'];

/* 显示左侧管理菜单 */

function showadminmenu() {
    $menu = & cls_adminpower::$power;

    $myrole = $GLOBALS['j']['user']['u_roleic'];
    foreach ($menu as $k => $v) {
        if ('group' == $v['type'] AND (false !== strpos($v['role'], $myrole))) {
            echo '<div class="sidebar">' . PHP_EOL;
            echo '  <label id="' . $k . '">' . $v['title'] . '<img src="/_images/arrow.png" id="arrow" /></label>' . PHP_EOL;
            echo '  <ul class="usermenu">' . PHP_EOL;

            foreach ($menu as $x) {
                if ($k == $x['pid'] AND (false !== strpos($x['role'], $myrole))) {
                    echo '<li><a href="' . admindir . $x['page'] . '">' . $x['title'] . '</a></li>' . PHP_EOL;
                }
            }
            echo '  </ul>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $this->headtitle ?></title>
        <link type="text/css" rel="stylesheet"  href="<?php echo admindir ?>css/base.css?t=<?php echo timestamp ?>" />
        <link type="text/css" rel="stylesheet"  href="<?php echo admindir ?>css/main.css?t=<?php echo timestamp ?>" />
        <link type="text/css" rel="stylesheet"  href="<?php echo admindir ?>css/plus.css?t=<?php echo timestamp ?>" />

        <link type="text/css" rel="stylesheet"  href="<?php echo admindir ?>css/menu_left.css?t=<?php echo timestamp ?>" />

        <script src="<?php echo webdir ?>_js/jquery-1.11.3.min.js?t=<?php echo timestamp ?>"></script>    
        <script src="<?php echo webdir ?>_js/main.js?t=<?php echo timestamp ?>"></script>
        <script src="<?php echo admindir ?>js/main.js?t=<?php echo timestamp ?>"></script>


        <script>


            /*给当前菜单加on*/
            function LeftMenuon() {
                var url = this.location.pathname;	//取路径和文件名

                if (url.indexOf('.') < 1) {  //如果“.”的位置小于1（没有），就不执行添加on的效果 
                    return(false);
                }




                $(".leftmenu a").each(function () {
                    var o = $(this);
                    if (o.attr('href').indexOf(url) > -1) {


                        o.addClass('on');
                    }
                })
            }

            $(document).ready(function () {

                LeftMenuon();

                $('#loginout').on('click', function () {
                    var href = $(this).attr('href');

                    $.getJSON(href, function (json) {
                        if ('y' == json['success']) {
                            window.location.href = '<?php echo admindir . "login.php" ?>';
                        }
                    })

                    return false;
                })

                /*提取cookie,供显隐藏菜单*/
                var hidemenu = getCookie('hidemenu');

                /*跟据cookie存的值，隐藏相应菜单*/
                $('#leftmenu label').each(function () {
                    var myid = $(this).attr('id');
                    
                    if(hidemenu.indexOf(myid)>-1){
                        $(this).next('ul').hide();
                    }
                    
                })

                /*收起菜单*/
                $("label").on('click', function () {
                    var myid = $(this).attr('id');

                    if ($(this).next('ul').is(":visible")) {
                        $(this).find('img').addClass('on');
                        $(this).next('ul').hide();
                    } else {
                        $(this).next('ul').show();
                        $(this).find('img').removeClass('on');
                    }

                    /*收集隐藏的菜单并存进cookie*/
                    hidemenu = '';
                    $('#leftmenu ul:hidden').each(function () {
                        hidemenu += ($(this).prev('label').attr('id') + ',');
                    })

                    setCookie('hidemenu', hidemenu, 7);
                    //$(this).children("img").toggleClass('on');
                });
            })


        </script>

        <!-- 每页调用自已的css,js -->
        <?php $this->headplus(); ?>
    </head>

    <body>

        <noscript><h1 class="noscript">您已禁用脚本，这样会导致页面不可用，请启用脚本后刷新页面</h1></noscript>

        <div class="top clearfix">
            <div class="main">
                <div class="fleft"><a href="<?php echo admindir ?>" class="logo">管理中心</a></div>
                <div class="fright">
                    <span><?php echo $data['user']['u_rolename'] ?>： <?php echo $data['user']['u_nick'] ?></span>
                    <span><a id="loginout" href="<?php echo admindir . 'login.php?act=loginout' ?>" class="">[注销]</a></span>
                </div>
            </div>
        </div>
        <div class="main clearfix">
            <div class="column1">
                <div class="leftmenu" id="leftmenu">
                    <?php
                    showadminmenu();
                    ?>

                    <!--                    <div class="sidebar">
                                            <input id="user" type="checkbox" />
                                            <label for="user">用户管理<img src="/_images/arrow.png" id="arrow" /></label>
                                            <ul class="usermenu">
                                                <li><a href="<?php echo admindir ?>user/group.php">用户组</a></li>
                                                <li style="display:none"><a href="<?php echo admindir ?>user/group.php">用户</a></li>
                                                <li><a href="<?php echo admindir ?>user/user.php">会员</a></li>
                                            </ul>
                                        </div>
                    
                                        <div class="sidebar">
                                            <input id="goods" type="checkbox" />
                                            <label for="goods">商品管理<img src="/_images/arrow.png" id="arrow" /></label>                        
                                            <ul class="goodsmenu">
                                                <li><a href="<?php echo admindir ?>goods/list.php">平台商品</a></li>
                                                <li><a href="<?php echo admindir ?>goods/store.php">商品仓库</a></li>
                                                <li><a href="<?php echo admindir ?>goods/counts.php">商品浏览统计</a></li>
                                                <li><a href="<?php echo admindir ?>goods/comstorealarm.php">店铺库存警报</a></li>
                                            </ul>
                                        </div>
                    
                                        <div class="sidebar">
                                            <input id="finance" type="checkbox" />
                                            <label for="finance">财务管理<img src="/_images/arrow.png" id="arrow" /></label>  
                                            <ul class="finance">
                                                <li><a href="<?php echo admindir ?>finance/moneyin.php">用户入款</a></li>
                                                <li style="display:none"><a href="<?php echo admindir ?>finance/moneyout.php">用户出款</a></li>
                                                <li style="display:none"><a href="<?php echo admindir ?>finance/account.php">资金统计</a></li>
                                                <li style="display:none"><a href="<?php echo admindir ?>finance/list.php">财务列表</a></li>
                                                <li style="display:none"><a href="<?php echo admindir ?>finance/moneysetting.php">财务设置</a></li>
                                                <li><a href="<?php echo admindir ?>finance/account.php">资金统计</a></li>
                                                <li style="diaplay:none"><a href="<?php echo admindir ?>finance/accountday.php">日资金统计</a></li>
                                                <li><a href="<?php echo admindir ?>finance/list.php">财务列表</a></li>
                                                <li><a href="<?php echo admindir ?>finance/takelist.php">提现审核</a></li>
                                            </ul>
                                        </div>
                    
                                        <div class="sidebar">
                                            <input id="vfinance" type="checkbox" />
                                            <label for="vfinance">赠款管理<img src="/_images/arrow.png" id="arrow" /></label>
                                            <ul class="vfinance">
                                                <li><a href="<?php echo admindir ?>vfinance/account.php">赠款统计</a></li>
                                                <li><a href="<?php echo admindir ?>vfinance/list.php">赠款记录</a></li>
                                            </ul>
                                        </div>
                    
                                        <div class="sidebar">
                                            <input id="business" type="checkbox" />
                                            <label for="business">业务管理<img src="/_images/arrow.png" id="arrow" /></label>
                                            <ul class="business">
                                                <li><a href="<?php echo admindir ?>business/bizer.php">商家用户</a></li>
                                                <li><a href="<?php echo admindir ?>business/company.php?ic=bizer">店铺管理</a></li>
                                                <li><a href="<?php echo admindir ?>business/order.php">定单管理</a></li>
                                                <li><a href="<?php echo admindir ?>business/device.php">设备管理</a></li> 
                                                <li><a href="<?php echo admindir ?>business/repair.php">设备维修</a></li>
                                                <li><a href="<?php echo admindir ?>business/patrol.php">补换货记录</a></li>
                                            </ul>
                                        </div>
                    
                                        <div class="sidebar">
                                            <input id="form" type="checkbox" />
                                            <label for="form">在线调查<img src="/_images/arrow.png" id="arrow" /></label>
                                            <ul class="form">
                                                <li><a href="<?php echo admindir ?>form/index.php">调查管理</a></li>
                    
                                            </ul>
                                        </div>                   
                    
                                        <div class="sidebar">
                                            <input id="data" type="checkbox" />
                                            <label for="data">数据管理<img src="/_images/arrow.png" id="arrow" /></label>
                                            <ul class="data">
                                                <li><a href="<?php echo admindir ?>data/index.php">数据维护</a></li>
                    
                                            </ul>
                                        </div>   -->

                </div>
            </div>
            <div class="column2">
                <?php $this->main(); ?>
            </div>
        </div>

        <div class="bottom main">
            Runtime <?php echo $data['server']['runtime'] ?> 秒,查询数据库
            <?php echo $data['server']['sqlquerynum'] ?> 次
        </div>  



    </body>


</html>



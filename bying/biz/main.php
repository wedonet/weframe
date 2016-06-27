<?php
$data = & $GLOBALS['j'];
require_once( syspath . '/biz/checkbiz.php' ); //检测权限
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!--<title><?php echo $this->headtitle ?></title>-->
        <title>商家管理中心</title>
        <link type="text/css" rel="stylesheet"  href="/biz/css/base.css?t=<?php echo timestamp ?>" />
        <link type="text/css" rel="stylesheet"  href="/biz/css/main.css?t=<?php echo timestamp ?>" />
        <link type="text/css" rel="stylesheet"  href="/biz/css/plus.css?t=<?php echo timestamp ?>" />
        <script src="/_js/jquery-1.11.3.min.js?t=<?php echo timestamp ?>"></script>    
        <script src="/_js/main.js?t=<?php echo timestamp ?>"></script>
        <script src="/biz/js/main.js?t=<?php echo timestamp ?>"></script>


        <script>


            /*给当前菜单加on*/
            function LeftMenuon() {
                var url = this.location.pathname;	//取路径和文件名

                if (url.indexOf('.') < 1) {  //如果“.”的位置小于1（没有），就不执行添加on的效果 
                    return(false);
                }




                $(".leftmenu a").each(function() {
                    var o = $(this);
                    if (o.attr('href').indexOf(url) > -1) {


                        o.addClass('on');
                    }
                })
            }

            $(document).ready(function() {
                LeftMenuon();

                $('#loginout').bind('click', function() {
                    var href = $(this).attr('href');

                    $.getJSON(href, function(json) {
                        if ('y' == json['success']) {
                            window.location.href = "/biz/service/login.php";
                        }
                    })

                    return false;
                })
            })


        </script>

        <!-- 每页调用自已的css,js -->
        <?php $this->headplus(); ?>
    </head>

    <body>

        <noscript><h1 class="noscript">您已禁用脚本，这样会导致页面不可用，请启用脚本后刷新页面</h1></noscript>

        <div class="top clearfix">
            <div class="main">
                <div class="fleft"><a href="/biz/index.php" class="logo"><?php echo $GLOBALS['j']['company']['title'] ?></a></div>
                <div class="fright">
                    <span><?php echo $data['user']['u_rolename'] ?>： <?php echo $data['user']['u_fullname'] ?></span>
                    <span><a id="loginout" href="/biz/service/login.php?act=loginout" class="">[注销]</a></span>
                </div>
            </div>
        </div>
        <div class="main clearfix">
            <div class="column1">
                <div class="leftmenu">
                    <div class="sidebar">
                        <h4>商品管理</h4>
                        <ul>
                            <li><a href="/biz/goods/list.php">商品列表</a></li>
                            <li><a href="/biz/goods/goodsaccount.php">商品售卖统计</a></li>
                        </ul>
                    </div>

                    <div class="sidebar">
                        <h4>财务管理</h4>
                        <ul>
                            <li><a href="/biz/finance/account.php">财务统计</a></li>
                            <li><a href="/biz/finance/list.php">财务记录</a></li>
                            <li><a href="/biz/finance/takelist.php">提现</a></li>
                        </ul>
                    </div>

                    <div class="sidebar">
                        <h4>业务管理</h4>
                        <ul>
                            <li><a href="/biz/business/history.php">补换货记录</a></li>
                            
                        </ul>
                    </div>

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



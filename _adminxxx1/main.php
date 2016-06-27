<?php
$data = & $GLOBALS['j'];

/* 显示左侧管理菜单 */

function showadminmenu() {
    $menu = & cls_adminpower::$power;
    $myrole = $GLOBALS['j']['user']['u_roleic'];
    foreach ($menu as $k => $v) {
        if ($myrole == 'sys' || (!empty($v['access_name']) && in_array($v['access_name'], $GLOBALS['allow_access']))) {
            $is_allow = TRUE;
        } else {
            $is_allow = FALSE;
        }
        if ('group' == $v['type'] && $is_allow) {
            echo '<div class="sidebar">' . PHP_EOL;
            echo '  <label id="' . $k . '">' . $v['title'] . '<img src="/_images/arrow.png" id="arrow" /></label>' . PHP_EOL;
            echo '  <ul class="usermenu">' . PHP_EOL;

            foreach ($menu as $x) {
                if ($myrole == 'sys' || (!empty($x['access_name']) && in_array($x['access_name'], $GLOBALS['allow_access']))) {
                    $is_allowx = TRUE;
                } else {
                    $is_allowx = FALSE;
                }
                if ($k == $x['pid'] AND $is_allowx ) {
                    echo '<li><a href="' . admindir . $x['page'] . '">' . $x['title'] . '</a></li>' . PHP_EOL;
                }
            }
            echo '  </ul>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
        }
    }
}
?><!DOCTYPE html>
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
                var url = this.location.pathname; //取路径和文件名

                if (url.indexOf('.') < 1) {  //如果“.”的位置小于1（没有），就不执行添加on的效果 
                    return(false);
                }


                $("#leftmenu a").each(function () {
                    var o = $(this);
                    if (o.attr('href').indexOf(url) > -1) {


                        o.addClass('on');
                    }
                })

            }

            $(document).ready(function () {

                $('#loginout').bind('click', function () {
                    var href = $(this).attr('href');
                    $.getJSON(href, function (json) {
                        if ('y' == json['success']) {
                            window.location.href = '<?php echo admindir . "login.php" ?>';
                        }
                    })

                    return false;
                });



                /*提取cookie,供显隐藏菜单*/
                var hidemenu = getCookie('hidemenu');
                //alert(hidemenu);
                /*跟据cookie存的值，隐藏相应菜单*/
                $('#leftmenu label').each(function () {
                    var myid = $(this).attr('id');


                    if (hidemenu.indexOf(myid) > -1) {
                        $(this).next('ul').hide();
                        $(this).find('img').addClass('on');
                    }

                })

                LeftMenuon();
                /*收起展开菜单*/
                $("#leftmenu label").on('click', function () {

                    if ($(this).next('ul').is(":visible")) {
                        $(this).next('ul').hide();
                        $(this).find('img').addClass('on');


                    } else {
                        $(this).next('ul').show();
                        $(this).find('img').removeClass('on');
                    }

                    /*收集隐藏的模块id存进cookie*/
                    hidemenu = '';
                    $('#leftmenu ul:hidden').each(function () {
                        hidemenu += ($(this).prev('label').attr('id') + ',');//隐藏ul的上一层label的id
                    })

                    //alert(hidemenu);

                    setCookie('hidemenu', hidemenu, 7);//存储cookie

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



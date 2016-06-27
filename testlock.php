<?php
/* 测试单片机开门情况 */

require_once(__DIR__ . '/global.php');

if (isset($_GET['act'])) {
    $act = $_GET['act'];
} else {
    $act = '';
}

switch ($act) {
    case '':
        myform();
        break;
    case 'getinfo':
        getinfo();
        break;
}

function myform() {
    ?><!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>门锁测试</title>
            <script src="/_js/jquery-1.11.3.min.js?t=5"></script>    
            <script src="/_js/main.js?t=5"></script>

            <style type="text/css">
                h2{
                    text-align:center;
                }
            </style>


            <script>
                $(document).ready(function () {
                    $('#submit').on('click', function () {
                        if ('' == $('#mac').val()) {
                            alert('请输入Mac地址');
                            return;
                        }
                        if ('' == $('#door').val()) {
                            alert('请输入门号');
                            return;
                        }


                        

                        //var baseurl = '';
                        //if ('outter' == $('#server').val()) {
                        //    baseurl = 'http://www.ejshendeng.com';
                        //} else {
                        //    baseurl = 'http://192.168.30.248';
                        //}

                        //var href = baseurl + ':1337/index.html/';

                        //href += ($('#mac').val() + '/');

                        //href += $('#door').val();

                        var href = '?act=getinfo&server=' + $('#server').val();
                        href += '&mac=' + $('#mac').val();
                        href += '&door=' + $('#door').val();

                        $.ajax({
                            cache: false,
                            type: 'GET',
                            url: href,
                            dataType: 'json', //返回json格式数据
                            success: function (json) {
                                if ('y' == json.success) {
                                    $('#submit').attr('disabled', 'disabled');
                                    
                                    $('#info').text('发送成功，10秒后重新发送');
                                    
                                    /*10秒后重新提交*/
                                    settimeOut(function(){
                                        $('#submit').removeAttr('disabled');
                                    }, 10000)
                                } else {
                                    $('#info').text(json.errmsg[0]);
                                }

                            },
                            error: function (xhr, type, error) {
                                alert('Ajax error:' + xhr.responseText);

                                //alert('Ajax error!');
                            }
                        })



                    })
                })
            </script>



            <!-- 每页调用自已的css,js -->
        </head>

        <body>
            <div style="margin:10px;">
                <h2 >门锁测试</h2>

                <noscript><h1 class="noscript">您已禁用脚本，这样会导致页面不可用，请启用脚本后刷新页面</h1></noscript>

                <select id="server">
                    <option value="outter" selected="selected">正式服务器</option>
                    <option value="inner">内网服务器</option>
                </select>

                <br /><br /><br />

                <input type="text" id="mac" placeholder="Mac地址" size="40" />

                <br /><br /><br />

                <input type="text" id="door" placeholder="门号 1，2，3..." size="40" />

                <br /><br /><br />

                <input type="button" id="submit" value="提交" /> &nbsp; &nbsp; &nbsp; <span id="info"></span>


            </div>
        </body>


    </html>


    <?php
}

function getinfo() {
    $server = $_GET['server'];
    $mac = $_GET['mac'];
    $door = $_GET['door'];

    if ('outter' == $server) {
        $server = 'http://www.ejshendeng.com:1337/index.html/';
    } else {
        $server = 'http://192.168.0.248:1337/index.html/';
    }

    $href = $server;
    $href .= $mac;
    $href .= '/' . $door;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $href);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出  
    $result = curl_exec($ch);
    curl_close($ch);

    echo $result;
}

<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

function headplus() {
    echo '<link rel="stylesheet" href="/_css/login.css" type="text/css"/>';
}

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 跟据act确定输出格式 */
        $jsonact = array('save',
            'vertify', //验证答案是否正确
            'sendsms' //发送短信验证码
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once(ApiPath . 'service/_reg.php'); //访问接口去



        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区
                break;
        }
    }

    function pagemain() {
        $j = & $GLOBALS['j'];
        $j['headtitle'] = '用户注册';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">用户注册</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <form method="post" action="?act=save" id="myform">
                <div class="loginputs">
                    <ul>
                        <li>
                            <input class="inp" type="text" name="u_mobile" id="u_mobile" value="" tabindex="1" maxlength="20" placeholder="手机号" />
                        </li>
                        <li>
                            <input class="inp" type="password" name="u_pass" id="u_pass" maxlength="20" placeholder="密码">
                        </li>
                        <li>
                            <input class="inp w60" type="text" name="codestr" id="codestr" value="" tabindex="1" maxlength="5" placeholder="请输入正确答案" /><div class="mycode" id="mycode"></div>
                        </li>
                        <li>
                            <input class="inp w60" type="text" name="smscode"  value="" tabindex="1" maxlength="6" placeholder="短信验证码"/>
                            <input type="button" id="sendsms" class="mycode" value="获取验证码" />
                        </li>

                    </ul>
                </div>   
                <div class="myinput">
                    <input class="btn" type="submit" tabindex="3" value="立即注册"/>
                </div>
                <div class="mya">
                    <span class="fright">已有账号？<a href="login.php" style="display: inline;">立即登录 ></a></span>
                </div>
            </form>

        </div>
            <div id="data_session" style="display:none"><?php echo $_SESSION[CacheName.'procomeurl'] ?></div><!--接受来时页-->
        <script type="text/javascript">
            var defaultsecond =60; //默认多长时间可以发送
            var second = defaultsecond;
            var timer;


            $(document).ready(function () {
                /*显示验证码*/
                $('#mycode').load('/_inc/getcode.php');


                /*刷新验证码*/
				 $('#mycode').removeAttr('disabled');
                 $('#mycode').on('click', function () {
                   $('#mycode').load('/_inc/getcode.php');
                })
                
				/*让获取短信验证码可点*/
                $('#sendsms').removeAttr('disabled');
                /*获取短信验证码*/
                $('#sendsms').on('click', function () {
                    getsmscode();
                })


                $('#u_mobile').focus();/*光标定位到用户名输入框*/
                
                /*注册*/
                $('#myform').on('submit', function () {
                    j_post($(this), function (json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/
                        /*保存成功，跳转到支付页*/
                        if ('y' == json.success)
                        {
                           var tts=$("#data_session").text()  ;
                          if(''!=tts)
                         { 
                             ttt = setTimeout('window.location.href="'+tts+'"', 2000);
                           var mess = new Array();
                           //alert('<li><a href='+tts+'>二秒后转入支付页.</a></li>');
                           mess['content'] ='<li><a href='+tts+'>二秒后转入来时页面.</a></li>' ;  
                           opdialog(mess);
                          }
                          else
                          {
                                 /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/
                          ttt = setTimeout("window.location.href='/member/index.php'", 2000);
                            var mess = new Array();
                            mess['content'] = '<li><a href="/member/index.php">二秒后转入个人中心.</a></li>';
                            /*弹出对话框*/
                            opdialog(mess);
                          }
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;

                })
            })

            function getsmscode() {
                var vertifyurl = '?act=vertify'; //检测验证码是否正确的地址

                /*检测验证码是否正确*/
                var codestr = $('#codestr').val();

                if ('' == codestr) {
                    alert('请输入答案');
                    $('#codestr').focus();
                    return false;
                }
                vertifyurl += '&codestr=' + codestr;

                /*检测是否输入手机*/
                var u_mobile = $('#u_mobile').val();
                if ('' == u_mobile) {
                    alert('请输入手机号');
                    $('#u_mobile').focus();
                    return false;
                }


                $.ajax({
                    type: 'POST',
                    url: vertifyurl,
                    dataType: 'json',
                    timeout: 60000,
                    success: function (json) {
                        if ('n' == json.success) {
                            alert(json.errmsg[0]);
                        } else {
                            $('#sendsms').attr('disabled', true);
                            sendsms(u_mobile); //发送验证码
                        }
                    },
                    error: function (xhr, type) {
                        //alert(xhr.responseText + '/' + type);
                        alert('Ajax error!' + xhr.responseText + '/' + type)
                    }
                })


            }

            function sendsms(u_mobile) {
                var smsurl = '?act=sendsms'; //发送短信验证码地址
                 var codestr = $('#codestr').val();//必须再次发送验证码
                smsurl+='&codestr=' + codestr;

                $.ajax({
                    type: 'POST',
                    url: smsurl + '&u_mobile=' + u_mobile,
                    dataType: 'json',
                    timeout: 60000,
                    success: function (json) {
                        //alert(json.success);
                        if ('y' == json.success) {
                            second = defaultsecond;
                            change();
                        } else {
                            alert(json.errmsg[0]);
							$('#sendsms').removeAttr('disabled');
                        }

                    },
                    error: function (xhr, type) {
                        //alert(xhr.responseText + '/' + type);
                        alert('Ajax error!' + xhr.responseText + '/' + type)
                    }
                })
            }
            /*函数change*/
            function change() {  //倒计时函数
                second--;   //秒数递减

                /*还有剩余时间*/
                if (second > 0) {
                    $('#sendsms').val(second + '秒后重新获取');
                    timer = setTimeout('change()', 1000);   //变化1秒/次
                }
                /*时间到了*/
                else {
                    $('#sendsms').val('获取验证码');
                    $('#sendsms').removeAttr('disabled');
                    clearTimeout(timer);
                }
            }
        </script>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>




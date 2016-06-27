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
        $jsonact = array(
            'save',
            'vertify', //验证答案是否正确
            'sendsms' //发送短信验证码
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once(ApiPath . 'service/_searchpass.php'); //访问接口去



        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区

                break;
        }
    }

    function pagemain() {
        $j = & $GLOBALS['j'];
        $j['headtitle'] = '找回密码';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">找回密码</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <form method="post" action="?act=save" id="myform">
                <div class="loginputs">
                    <ul>
                        <li>
                            <input class="inp" type="text" name="u_mobile" id="u_mobile" value="" tabindex="1" maxlength="20" placeholder="手机号" />

                        </li>
                        <li>
                            <input class="inp w60" type="text" name="codestr" id="codestr" value="" tabindex="1" maxlength="5" placeholder="请输入正确答案" /><div class="mycode" id="mycode"></div>
                        </li>
                        <li>
                            <input class="inp w60" type="text" name="smscode"  value="" tabindex="1" maxlength="6" placeholder="短信验证码"/>
                            <input type="button" id="sendsms" class="mycode" value="获取验证码" /> 
        <!--                            <input class="inp w60" type="text" name=""  value="" tabindex="1" maxlength="5" placeholder="短信验证码"/><div class="mycode">获得验证码</div>                        -->
                        </li>
                        <li>
                            <input class="inp" type="password" name="u_pass" id="u_pass" maxlength="20" placeholder="新密码（6-20数字或字母）">
                        </li>
                        <li>
                            <input class="inp" type="password" name="u_pass2" id="u_pass2" maxlength="20" placeholder="确认密码（与新密码一致）">
                        </li>
                    </ul>
                </div>   
                <div class="myinput">
                    <input class="btn" type="submit" tabindex="3" value="提  交"/>
                </div>

            </form>
        </div>

   <div id="data_session" style="display:none"><?php echo $_SESSION[CacheName.'procomeurl'] ?></div><!--接受来时页-->
        <script type="text/javascript">

            var defaultsecond = 60;//默认60秒发送
            var second = defaultsecond;
            var timer;

            $(document).ready(function () {


                $('#mycode').load('/_inc/getcode.php')//网页载入后显示验证码

                //点击刷新本页验证码
                $('#mycode').removeAttr('disabled');
                $('#mycode').on('click', function () {
                    $('#mycode').load('/_inc/getcode.php');
                })


                $('#u_mobile').focus(); //定位光标到用户名框

                //获取短信验证码
                $('#sendsms').removeAttr('disabled');
                $('#sendsms').on('click', function () {
                    getsmscode(); //获取验证码函数，本页已写
                })

                //提交
                $('#myform').on('submit', function () {
                    j_post($(this), function (json) {
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
                                ttt = setTimeout("window.location.href='/service/login.php'", 2000);
                                var mess = new Array();
                                mess['content'] = '<li><a href="/service/login.php">二秒后转入登录页面.</a></li>';
                                /*弹出对话框*/
                                opdialog(mess);
                          }
                            
                            
                               
                            
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    });
                    return false;
                });
            })



            //短信验证码函数getsmscode
            function getsmscode() {
                var vertifyurl = '?act=vertify'; //检验验证码是否正确的地址act为vertify

                //检测验证码是否正确  //为空提示，聚焦输入  //////////////////////////////////?????目前判断的是为空，并没有判断输入的正确与否？
                var codestr = $('#codestr').val();
                if ('' == codestr) {
                    alert('请输入正确答案');
                    $('#codestr').focus();
                    return false;
                }

                vertifyurl += '&codestr=' + codestr; //将地址带上输入的验证码值


                //检测是否输入手机号 //定义变量u_mobile,取#u_mobile的值 //未输入提示错误 //聚焦手机输入框
                var u_mobile = $('#u_mobile').val();
                if ('' == u_mobile) {
                    alert('请输入手机号');
                    $('#u_mobile').focus();
                    return false;
                }


                $.ajax({
                    type: 'POST',
                    url: vertifyurl, //上面定义的检验验证码的地址
                    dataType: 'json', //数据类型json
                    timeout: 60000,
                    success: function (json) {
                        if ('n' == json.success) {
                            alert(json.errmsg[0]); //如果json返回错误信息，显示错误
                        } else {
                            $('#sendsms').attr('disabled', true); //正确：发送按钮不可点，并发送验证码到手机
                            sendsms(u_mobile);
                        }
                    },
                    error: function (xhr, type) {
                        alert('Ajax error!' + xhr.responseText + '/' + type); ////////////////////////////////??????????????????????????     
                    }
                })
            }  //getsmscode函数结束



            //发送短信验证码函数
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


            //倒计时函数
            function change() {
                second--; //秒数递减

                /*还有剩余时间*/
                if (second > 0) {
                    $('#sendsms').val(second + '秒后重新获取');
                    timer = setTimeout('change()', 1000); //变化1秒/次
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




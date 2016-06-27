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
        $jsonact = array('loginin', 'loginout');
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once(ApiPath . 'service/_login.php'); //访问接口去


        switch ($this->act) {
            case '':
                $this->pagemain(); //主内容区
                break;
			case 'loginin':
                break;
//            case 'loginout':
//                $this->pagemain(); //主内容区
//                break;
				
			//case 'loginin':
         //       break;
            //case 'loginout':
            //    $this->pagemain(); //主内容区
           //     break;
// origin/debug
        }
    }

    function pagemain() {

        $j = & $GLOBALS['j'];

        $j['headtitle'] = '会员登录';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">会员登录</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <form method="post" action="?" id="mylogin">
             <input type="hidden" name="act" value="loginin" />
                <div class="loginputs">
                    <ul>
                        <li>
                            <input class="inp" type="text" name="u_mobile" id="u_mobile" value="" tabindex="1" maxlength="20" placeholder="手机号" />
                        </li>
                        <li>
                            <input class="inp" type="password" name="u_pass" tabindex="2" maxlength="20" placeholder="密码"/>
                        </li>
                        <li style="display:none">
                            <input class="inp w60" type="text" name=""  value="" tabindex="1" maxlength="5" placeholder="验证码"/>                            <div class="mycode">3+4=?</div>
                        </li>

                    </ul>
                </div>   
                <div class="myinput">
                    <input class="btn" type="submit" tabindex="3" value="登 录"/>
                </div>
                <div class="mya">
                    <span class="fleft"><a href="reg.php">快速注册！</a></span>
                    <span class="fright"><a href="searchpass.php" style="text-align:right">找回密码 ></a></span>
                </div>
            </form>
              
        </div>
         <div id="data_session" style="display:none"><?php echo $_SESSION[CacheName.'procomeurl'] ?></div><!--接受来时页-->
        <script>
            $(document).ready(function () {
                $('#u_mobile').focus();/*光标定位到用户名输入框*/
                $('#mylogin').on('submit', function () {
                   
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
                    });
                    return false;

                });
            })
        </script>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>




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
        $jsonact = array(
           
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }


        require_once(ApiPath . 'member/_changepass.php'); //访问接口去
        require_once('checkmember.php'); //检测权限

        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区

                break;
        }
    }

    function pagemain() {

        $j = & $GLOBALS['j'];

//        $j['headtitle'] = '重置密码';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">重置密码</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
             <form method="post" action="?act=save" id="myform">
                <div class="loginputs">
                    <ul>
                        <li>
                            <input class="inp" type="password" name="u_passold" id="u_passold" maxlength="20"  placeholder="原密码">
                        </li>
                        <li>
                            <input class="inp" type="password" name="u_pass" id="u_pass" maxlength="20" placeholder="新密码(6-20位数字或字母)">
                        </li>
                        <li>
                            <input class="inp" type="password" name="u_pass2" id="u_pass2" maxlength="20" placeholder="确认密码(与新密码一致)">
                        </li>                       
                    </ul>
                </div>   
                <div class="myinput">
                    <input class="btn" type="submit" tabindex="3" value="提  交"/>
                </div>
            </form>

        </div>
        <script type="text/javascript">

				$(document).ready(function() { 
                                        
                                        $('#u_passold').focus();//网页载入后光标自动定位到用户名输入框
					$('#myform').bind('submit', function() {
						j_post($(this), function(json) {
							/*给当前表单添加on,表示正提交这个表单，加到date里*/
							
			
							/*保存成功，跳转到支付页*/
							if ('y' == json.success)
							{
								/*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

								ttt = setTimeout("window.location.href='/member/index.php'", 2000);
								
								var mess=new Array(); 
								
								mess['content'] = '<li>保存成功！</li>';
					

								/*弹出对话框*/
								opdialog(mess);
							}
							else { //保存失败，显示失败信息
								errdialog(json);
							}
						})
						return false;
					})
				})

			</script>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);

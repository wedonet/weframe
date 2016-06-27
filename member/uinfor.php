<?php
require_once(__DIR__ . '/../global.php');
require_once(syspath . '/_style/cls_template.php');

function headplus() {
    echo '<link rel="stylesheet" href="/_css/member.css" type="text/css"/>';
}

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        require_once(ApiPath . 'member/_uinfor.php'); //访问接口去
        require_once( syspath . 'member/checkmember.php' ); //检测权限

        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区

                break;

        }
    }

    function pagemain() {

        $j = & $GLOBALS['j'];
        $u_gic = $this->get('u_gic'); //取带过来的u_gic

//        $j['headtitle'] = '个人信息';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">个人信息</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <form method="post" action="?act=save" id="myform">
                <div class="myinfors">               
                    <ul>                    
                        <li>会员ID<input class="input0" readonly="readonly" name="id" type="text" value="<?php echo $j['user']['id'] ?>" /></li>
                        <li>手机号码<input class="input0" readonly="readonly" name="u_mobile" type="text" value="<?php echo $j['user']['u_mobile'] ?>" /></li>
                        <li class="noboder">昵称<input class="input1" maxlength="20" type="text" name="u_nick" id="u_nick" value="<?php echo $j['user']['u_nick'] ?>"></li>
                    </ul>   
                    <div class="myinput">
                        <input class="btn" type="submit" value="保  存">
                    </div>

                </div> 
            </form>
        </div>
        
        <script type="text/javascript">

				$(document).ready(function() { 
                                        
                                        $('#u_nick').focus();//网页载入后光标自动定位到用户名输入框
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
?>
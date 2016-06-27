<?php

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

/*先去要数据
 * 再写模板
 */



/*把数据给模板的数据全据变量j*/

class myclass extends cls_template{
    
    function __construct() {
	require_once(ApiPath . 'biz/service/_login.php'); //访问接口去

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->mainframe(); //主内容区
                break;
			case 'loginin':
                break;
			case 'loginout':
				//$this->mainframe(); //主内容区
				break;
        }
    }  
    
    function mainframe(){
		?>
			<!DOCTYPE html>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<title>商家登录</title>
			<link type="text/css" rel="stylesheet"  href="/biz/css/base.css?t=1" />
			<link type="text/css" rel="stylesheet"  href="/biz/css/main.css?t=1" />
			<link type="text/css" rel="stylesheet"  href="/biz/css/plus.css?t=1" />
			<script src="/_js/jquery-1.11.3.min.js?t=1"></script>    
			<script src="/_js/main.js?t=1"></script>
			<script src="/biz/js/main.js?t=1"></script>

			<script type="text/javascript">

				$(document).ready(function() { 
                                        
                                        $('#u_name').focus();//网页载入后光标自动定位到用户名输入框
					$('#myform').bind('submit', function() {
						j_post($(this), function(json) {
							/*给当前表单添加on,表示正提交这个表单，加到date里*/
							
			
							/*保存成功，跳转到支付页*/
							if ('y' == json.success)
							{
								/*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

								ttt = setTimeout("window.location.href='../index.php'", 2000);
								
								var mess=new Array(); 
								
								mess['content'] = '<li><a href="../index.php">二秒后自动进入商家后台</a></li>';
					

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

        
			</head>

			<body>
				<noscript><h1 class="noscript">您已禁用脚本，这样会导致页面不可用，请启用脚本后刷新页面</h1></noscript>
                                
				<div class="main">
					<form method="post" action="?" id="myform">
                                            
                                           
						<input type="hidden" name="act" value="loginin" />
                                                
                                                <div class="login">
                                                <div class="title">商家登录</div>
                                                <input type="text" name="u_name" id="u_name" value="" size="20" placeholder="用户名" />
                                                
                                                <input type="password" name="u_pass" value="" size="20" placeholder="密码"/>
                                                
                                                <input type="submit" value=" 登 录 " size="20" />
                                                </div>
												
					</form>
					
				</div>
				

			</body>


			</html>




		<?php
        
     
    }

    
    
}

$tp = new myclass();
unset($tp);
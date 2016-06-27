<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

function headplus() {
    echo '<link rel="stylesheet" href="/_css/login.css" type="text/css"/>';
}

class myclass extends cls_template {

    function __construct() {
        parent::__construct(); //执行上级的构造函数
        /* 跟据act确定输出格式 */
        $jsonact = array('nsave'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }
        require_once(ApiPath . 'bying/joinus/_form.php'); //访问接口去 
        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区

                break;
        }
    }

    function pagemain() {


        $doorid = $this->get('doorid');

        // $j = & $GLOBALS['j'];
        //$account = & $j['account'];
        // $formid = $this->get('formid');
        // $form = $j['form'];
        $j['headtitle'] = '招商加盟';
        require_once(syspath . '_public/header.php');
        ?>
        <div class="title">招商加盟</div>
        <!--        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>-->
        <a class="titlebg" id='carback' href="joinus.php?doorid=<?php echo $doorid ?>"></a>
        <div class="main">

            <form method="post" action="?act=nsave" id="myform">  
                <div class="loginputs">
                    <ul>
                        <li>                      
                            <input class= "inp" type="text" maxlength="6" name="f_name" placeholder="请输入您的姓名（必填）" value=""/>                          
                        </li>
                        <li>
                            <input class="inp" type="text" maxlength="11" name="f_mobile" id="f_mobile" placeholder="请输入您的手机号码（必填）"/>    
                        </li>
                        <li>
                            <input class="inp" type="text" maxlength="30" name="f_field" id="f_field" placeholder="请输入您所从事的行业"/>
                        </li>
                        <li style="height:auto;">
                            <textarea class="" style="width:97%;border-radius:3px;line-height: 23px;font-size: 1.6rem;padding-left: 2%;" cols="50" rows="6" name="f_message" maxlength="150" placeholder="留言（字数150字内）" ></textarea>
                        </li>                  
                    </ul>

                    <div> 
                        <input type="submit" value="提 交" class="btn"/>
                    </div>
                </div>      
            </form>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {

                /*提交问卷*/
                $('#myform').bind('submit', function () {

                    j_post($(this), function (json) {

                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            ttt = setTimeout("window.location.href='../index.php?d=" + <?php echo $doorid ?> + "'", 2000);

                            var mess = new Array();

                            mess['content'] = '<a href="../index.php?d=' +<?php echo $doorid ?> + '" style="font-size:1.3rem;">提交成功，客服人员会在24小时内与您联系!</a>';
                            /*弹出对话框*/
                            opdialog(mess);
                        } else { //保存失败，显示失败信息
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
<?php
/* 用户出款 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_moneyout.php'; //访问接口去
        require_once(adminpath.'checkpower.php'); //检测权限
        $this->addcrumb('用户出款');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'searchuser'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'creat':
                $this->fname = 'myform'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;

            /* 下面几个不需要渲染 */
            case 'save':
                break;
        }
    }

    function searchuser() {
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="dosearch" />

            <input type="text" value="" name="u_name" />
            <input type="submit" value="搜索" />

        </form>
        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


                        /*保存成功，跳转到支付页*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?act=creat'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?ic=bizer">搜索成功，即将转入出款页面！</a></li>';


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
    }

    function myform() {

        $j = & $GLOBALS['j'];
        $data = $j['data'];

        $this->addcrumb('出款');
        crumb($this->crumb);
        ?>


        
        <table class="table1" cellspacing="1" >
            <tr>
                <th>
                用户信息:
            </th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            </tr>
            <tr>
                    <td width="80">用户id：</td>
                    <td width="150"><?php echo $j['data']['uid'] ?></td>
                
                <td width="80">用户昵称：</td>
                <td><?php echo $j['data']['unick'] ?></td>
            </tr>
                <tr>
                    <td  width="80">用户名：</td>
                    <td><?php echo $j['data']['uname'] ?></td>
                
                    <td  width="80">余额：</td>
                    <td><?php echo $j['data']['mytotal'] ?></td>
                </tr>

            </table>
        
        <p></p>
        
        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="save" />

            <table class="table1" cellspacing="1" >
             <tr>
                <th>
                出款:
            </th>
            <th>&nbsp;</th>
            
            </tr>
                <tr>
                    <td width="100">金额</td>
                    <td width="*"><input type="text" name="mytotal" id="mytotal" value="" size="20"></td>
                </tr>

                <tr>
                    <td>出款种类</td>
                    <td>
                        <select name="mytype" id="mytype">
                            <?php
                            foreach ($GLOBALS['j']['moneytype'] as $v) {
                                echo '<option value =" ' . $v['ic'] . '">' . $v['title'] . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>


                <td>原始凭证日期</td>
                <td><input type="text" name="stime" id="stime" value="" size="20"></td>



                <tr>
                    <td>原始凭证号</td>
                    <td><input type="text" name="formcode" id="formcode" value="" size="20"></td>
                </tr>

                <tr>
                    <td>备注</td>
                    <td><textarea type="text" name="other" id="other" rows="3" cols="60"></textarea></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="保存" class="submit1"></td>
                </tr>
            </table>
        </form>
        
        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/
                        
        
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);
                            
                            var mess=new Array(); 
                            
                            mess['content'] = '<li><a href="?">二秒后自动返回出款页面</a></li>';
                            

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
    }

}

$tp = new myclass();
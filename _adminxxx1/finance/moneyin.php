<?php
/* 给用户入款 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 什么情况下必须返回json格式 */
        $jsonact = array('json'
            , 'dofinduser'
            , 'save'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_moneyin.php'; //访问接口去

        require_once(adminpath . 'checkpower.php'); //检测权限

        $this->addcrumb('用户入款');

        switch ($this->act) {
            case'':
                $this->fname = 'thismain'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;

            case 'myform': //入款表单
                $this->fname = 'myform';
                require_once(adminpath . 'main.php'); //主模板
                break;
        }
    }

    function thismain() {
        crumb($this->crumb);
        ?>
        <form action="?act=dofinduser&amp;u_gic=admin" method="post" id="myform1">
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <td width="30%"><strong>管理员入款</strong> &nbsp; (请输入管理员用户名)</td>
                    <td width="*"><input name="u_name"  value="" size="20" />
                        <input type="submit" value="提交" />
                    </td>
                </tr>
            </table>
        </form>

        <form action="?act=dofinduser&amp;u_gic=bizer" method="post" id="myform2">
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <td width="30%"><strong>商家入款</strong> &nbsp; (请输入商家用户名)</td>
                    <td width="*"><input name="u_name"  value="" size="20" />
                        <input type="submit" value="提交" />
                    </td>
                </tr>
            </table>
        </form>

        <form action="?act=dofinduser&amp;u_gic=user" method="post" id="myform3">
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <td width="30%"><strong>个人入款</strong> &nbsp; (请输入个人手机号)</td>
                    <td width="*"><input name="u_mobile"  value="" size="20" />
                        <input type="submit" value="提交" />
                    </td>
                </tr>
            </table>
        </form>



        <div class="tip1">
            <dl>
                <dt>说明:</dt>
                <dd>用户入款：向个人或商家账户的入款，实际入款时将同时为平台账户增加相应款项</dd>
            </dl>
        </div>

        <script type="text/javascript">

            $(document).ready(function () {

                $('#myform1, #myform2, #myform3').on('submit', function () {
                    j_post($(this), function (json) {
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            window.location.href = '?act=myform&uid=' + json.uid + '&u_gic=' + json.u_gic;
                        } else { //保存失败，显示失败信息
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

        crumb($this->crumb);
        ?>
        <form action="?act=save" method="post" id="myform">
            <input type="hidden" name="uid" value="<?php echo $j['myuser']['id'] ?>" />
            <input type="hidden" name="u_gic" value="<?php echo $j['myuser']['u_gic'] ?>" />
            
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th colspan="2">用户信息</th>
                </tr>
                <tr>
                    <td width="30%">用户ID</td>
                    <td><?php echo $j['myuser']['id'] ?></td>
                </tr>
                <tr>
                    <td>用户名</td>
                    <td><?php echo $j['myuser']['u_name'] ?></td>
                </tr>
                <tr>
                    <td>用户昵称</td>
                    <td><?php echo $j['myuser']['u_nick'] ?></td>
                </tr>
                <tr>
                    <td>用户类型</td>
                    <td><?php echo $j['myuser']['u_gname'] ?></td>
                </tr>
            </table>
            <br />
            <br />
            <table class="table1 j_list" cellspacing="0">

                <tr>
                    <th colspan="2">入款</th>
                </tr>
                <?php
                /* 商家显示出商家名称 */
                if ('bizer' == $j['myuser']['u_gic']) {
                    echo '<tr>' . PHP_EOL;
                    echo '  <td>店铺</td>' . PHP_EOL;
                    echo '  <td>' . PHP_EOL;
                    echo '  <select name="comid" id="selectcomid">' . PHP_EOL;
                    echo '  <option value="">选择入款店铺</option>' . PHP_EOL;

                    foreach ($j['myuser']['comlist'] as $v) {
                        echo '<option value="' . $v['id'] . '">' . $v['title'] . '</option>' . PHP_EOL;
                    }

                    echo '  </select>' . PHP_EOL;
                    echo '  </td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
                ?>
                <tr>
                    <td>金额</td>
                    <td width="*"><input name="myvalue" value="" size="20" style="text-align:right" /> 元</td>
                </tr>


                <tr>
                    <td>入款方式</td>		
                    <td>
                        <select name="myway" id="myway">
                            <option value=""></option>

                            <?php
                            foreach ($j['myway'] as $k => $v) {
                                echo '<option value="' . $k . '">' . $v['title'] . '</option>';
                            }
                            ?>				

                        </select>

                    </td>
                </tr>


                <tr>
                    <td>原始凭证日期</td>
                    <td width="*"><input  type="text" name="formdate" value="" id="formdate" size="20"/></td>
                </tr>
                <tr>
                    <td>原始凭证号</td>
                    <td width="*"><input name="formcode" value="" size="20"/></td>
                </tr>
                <tr>
                    <td>备注</td>
                    <td width="*"><textarea name="other" cols="80" rows="3"></textarea></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input class="submit1" type="submit" value=" 提 交 "/></td>
                </tr>
            </table>
        </form>

        <div class="tip1">
            <dl>
                <dt>说明</dt>
                <dd>实际入款时将同时为平台账户增加相应款项</dd>
                <dd>账户总额=充值总额+收入总额</dd>

            </dl>
        </div>


        <script type="text/javascript">

            $(document).ready(function () {
                /*如果有选择店铺,且只有一个店铺,那么自动选中第一个*/
                if($('#selectcomid').length>0){
                    if( 2==$('#selectcomid option').length){
                        $('#selectcomid option').eq(1).attr('selected', 'selected');
                    };
                }

                $('#myform').bind('submit', function () {
                    j_post($(this), function (json) {

                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回入款页.</a></li>';


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
    }

}

$tp = new myclass(); //调用类的实例
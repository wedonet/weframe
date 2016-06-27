<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');
/* 先去要数据
 * 再写模板
 */

/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        require_once(ApiPath . '/biz/finance/_takelist.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->addcrumb('提现');
        $this->comid = $this->rid('comid');
        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            case 'take':
                $this->fname = 'myform'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];
//$comid = $this->get('comid');

        crumb($this->crumb);
        ?>

        <!--提现信息-->
        <div style="font-weight:bold">
            提款中：<?php echo $j['account']['doingtake'] / 100 ?>元 &nbsp;
            可提款：<?php echo $j['account']['acanuse'] / 100 ?>元

            &nbsp;&nbsp;&nbsp;<a href="?act=take" >提现</a>

        </div>
        &nbsp;
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="60">ID</th>
                <th width="150">申请时间</th>
                <th width="*">申请金额(元)</th>
                <th width="*">申请人</th>
                <th width="*">支付宝账户</th>
                <th width="*">状态</th>
                <th width="*">备注</th>
                <th width="*" style="display:none">操作</th>


            </tr>

        <?php
        foreach ($list['rs'] as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
            echo '<td>' . $v['myvalue']/100 . '</td>' . PHP_EOL;
            echo '<td>' . $v['fullname'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['payaccount'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['mystatustitle'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
            echo '<td style="display:none">' . PHP_EOL;

            if ('new' == $v['mystatus']) {
                echo '<a href="?act=cancel&id=' . $v['id'] . '" title="取消当前申请？" class="confirmedit">取消</a> &nbsp; ' . PHP_EOL;
            }
            echo ' </td>' . PHP_EOL;


            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.confirmedit').j_confirmedit(function(json) {
                    /*操作成功刷新页面*/
                    if ('y' == json.success) {
                        document.location.reload();
                    }

                })
            })
        </script>


        <?php
        $this->pagelist($j['list']['total']);
    }

    //申请提现
    function myform() {
        $j = & $GLOBALS['j'];
        $list = & $j['list'];
        $this->addcrumb('申请提现');
        $comid = $this->get('comid');

        crumb($this->crumb);
        ?>

        <form method="post" action="?" id="myform">            
            <input type="hidden" name="act" value="nsave" />  
            <input type="hidden" name="comid" value="<?php echo $comid ?>" /> 
            <table class="tableform" cellspacing="1" >
                <tr>
                    <td width="65">可提款</td>
                    <td width="*"><?php echo $j['account']['acantake'] / 100 ?>元</td>
                </tr>

                <tr>
                    <td width="*">提现金额</td>
                    <td width="*"><input type="text" name="myvalue" id="myvalue" value="" size="20">元<span style="color:#999">（提现金额不得大于可提款金额！）</span></td>
                </tr>     
                <tr>
                    <td width="*">支付宝账户</td>
                    <td width="*"><input type="text" name="payaccount" id="payaccount" value="" size="30"></td>
                </tr> 
                <tr>
                    <td width="*">账户姓名</td>
                    <td width="*"><input type="text" name="fullname" id="fullname" value="" size="30"></td>
                </tr>
                <tr>
                    <td width="*">到账时间</td>
                    <td width="*">3-10个工作日</td>
                </tr>


                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="申请提现" class="submit1"></td>
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

                            ttt = setTimeout("window.location.href='?comid=<?php echo $this->comid ?>'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?comid=<?php echo $this->comid ?>">二秒后自动刷新提现列表.</a></li>';



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
unset($tp);
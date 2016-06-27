<?php
/* api 管理中心 - 神灯替我付 - 查看答案 */

require_once(__DIR__ . '/../../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* ============================== */
        /* 什么情况下必须返回json格式 */

        $jsonact = array('json'
            , 'esave'
            , 'nsave'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once AdminApiPath . 'form/_answer.php'; //访问接口去

        require_once( adminpath . 'checkpower.php'); //检测权限

        $this->addcrumb('查看答案');

        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
            case 'export':
                $this->doexport();
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        crumb($this->crumb);
        ?>

        <!--导航-->
        <div class="top">


            <div class="name01"> 
                <span>
                    <?php
                    echo $j['form']['title']
                    ?>
                </span>
            </div>


        </div>
        <!--操作区css区分-->
        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                <input type="hidden" value="<?php echo $j['form']['id'] ?>"id="id" name="id">
                <input type="hidden" value="<?php echo $j['ic'] ?>"id="ic" name="ic">
                <input type="hidden" name="act" id="act" value="" />

                &nbsp;   

                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
                <input type="button" id="btnexport" value=" 导出 ">
            </form>
        </div>



        <script type="text/javascript">

            $(document).ready(function () {
                /*点导出，把act的值设为导出，然后提交表单*/
                $('#btnexport').on('click', function () {

                    $('#act').val('export');

                    $('#formsearch').submit();
                })

                $('#btnsearch').on('click', function () {
                    $('#act').val('');
                    return true;
                })

            })

        </script>



        <?php
        if ('y' != $j['success']) {
            $this->showerrlist($j['errmsg']); //把错误信息打印出来
        } else {

            $this->showlist($j);
        }
    }

    function showlist(&$j) {

        $list = & $j['list']['rs'];
        $total = & $j['list']['total'];
        ?>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                 <th width="50">ID</th>
                <th width="*">问卷ID</th>
                <th width="*">姓名</th>
                <th width="*">手机号</th>
                <th width="*">年龄</th>
                <th width="*">婚育</th>
                <th width="*">购车</th>                         
                <th width="*">答卷时间</th>
            </tr>

            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['formid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_fullname'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_mobile'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_age'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_marriage'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_car'] . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>


        <?php
        $this->pagelist($j['list']['total']);
    }

    function doexport() {

        $list = & $GLOBALS['j']['list'];

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=renshou.xls ");
        header("Content-Transfer-Encoding: binary ");
        ?>
        <table border="1">
            <tr>
                <th width="50">ID</th>
                <th width="50">问卷ID</th>
                <th width="50">姓名</th>
                <th width="*">手机号</th>
                <th width="*">年龄</th>
                <th width="*">婚育</th>
                <th width="*">购车</th>                         
                <th width="*">答卷时间</th>
            </tr>

            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['formid'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_fullname'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_mobile'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_age'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_marriage'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_car'] . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
//                echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>
        
        <table border="0">
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
               
        </table>


        <?php
    }

}

$tp = new myclass(); //调用类的实例
unset($tp);

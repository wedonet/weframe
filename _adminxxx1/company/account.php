<?php
/* 铺位管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();
        /* 提取数据 */
        require_once(AdminApiPath . 'company/_account.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb($j['company']['title']); //crumb加上公司名
        $this->addcrumb('财务管理');

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;

            case 'edit':
                $this->fname = 'formedit';
                require_once(adminpath . 'main.php');
                break;
            case 'creat':
                $this->fname = 'myform';
                require_once(adminpath . 'main.php');
                break;
            case 'esave':
            case 'nsave':
                break;
        }
    }

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];
         
        $jsonarr=  json_encode($j['search']);
      
        crumb($this->crumb);

        $comid = & $this->comid;

        require_once('biztab.php'); /* 商家业务选项卡 */
        ?>
        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 
                <input type="hidden" name="act" id="act" value="" />
                 <input type="hidden" name="comid" id="comid" value="<?php echo $this->comid?>" />

                <?php echo $j['search']['comname'] ?> &nbsp;  


                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
             <select name="myfromname" id="myfromname">
                    <option value="">全部</option>
                    <option value="shendeng">神灯订单</option>
                    <option value="diannei">店内有售</option>

                </select>
                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
            </form>
        </div>
        <p></p>
        统计信息
        <div style="font-weight:bold">
            入款：<?php
            if (isset($j['account']['myvalue'])) {
                echo $j['account']['myvalue'] / 100;
            } else {
                echo 0;
            }
            ?>元 &nbsp;
            出款：<?php
            if (isset($j['account']['myvalueout'])) {
                echo $j['account']['myvalueout'] / 100;
            } else {
                echo 0;
            }
            ?>元 &nbsp; 
            成交：<?php
            if (isset($j['account']['mycount'])) {
                echo $j['account']['mycount'];
            } else {
                echo 0;
            }
            ?>笔
        </div>
        <script type="text/javascript">
            $(document).ready(function(){
             
                var jsonarr=<?php echo $jsonarr ?>
        
                $("#myfromname").val(jsonarr.myfrom);
            })
        </script>
        <?php
        if ('n' == $this->j['success']) {
            $this->showerrlist($this->j['errmsg']);
        } else {
            ?>
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="50">ID</th>
                    <th width="*">操作人ID</th>

                    <th width="*">入款（元）</th>
                    <th width="*">出款（元）</th> 
                    <th width="*">余额（元）</th>
                    <th width="*">款项类型</th> 
                    <th width="*">支付方式</th> 
                    <th width="*">原始凭证号</th> 
                        <th width="*">订单类型</th>
                    <th width="*">时间</th>
                    <th width="*">备注</th>


                </tr>

                <?php
                foreach ($list as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['duid'] . '</td>' . PHP_EOL;

                    echo '<td>' . sprintf("%.2f", $v['myvalue'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['myvalueout'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . sprintf("%.2f", $v['mytotal'] / 100) . '</td>' . PHP_EOL;
                    echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['mywayname'] . '</td>' . PHP_EOL;

                    echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
                         echo '<td>' . $v['myfromname'] . '&nbsp;</td>' . PHP_EOL;
                    echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
                    if ('3010' != $v['mytype']) {
                        echo '<td>' . $v['other'] . '</td>' . PHP_EOL;
                    } else {
                        echo '<td>定单ID:' . $v['orderid'] . '</td>' . PHP_EOL;
                    }


                    echo '</tr>' . PHP_EOL;
                }
                ?>
            </table>

            <?php
            $this->pagelist($j['list']['total']);
        }
    }

}

$tp = new myclass(); //调用类的实例
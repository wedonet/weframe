<?php
/* 补换货记录 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        require_once AdminApiPath . 'business' . DIRECTORY_SEPARATOR . '_patrol.php'; //访问接口去
        require_once(adminpath . 'checkpower.php'); //检测权限
        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->addcrumb('补换货记录');

        switch ($this->act) {
            case '':
                $this->fname = 'pagemain'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
        }
    }

    function pagemain() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $jsonstr = json_encode($j['search']); //将数组字符串化


        crumb($this->crumb);
        ?>
        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 
                <input type="hidden" name="act" id="act" value="" />

                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
                <select name="mytype" id="mytype">
                    <option value="">类型</option>
                    <option value="replenish">补货</option>
                    <option value="change">换货</option>
                </select>
                &nbsp;&nbsp;所属店铺&nbsp;<input type="text" name="comname"  value="<?php echo $j['search']['comname'];?>" placeholder="店铺名称"/>
                &nbsp;&nbsp;铺位&nbsp;<input type="text" name="placetitle" value="<?php echo $j['search']['placetitle'];?>" placeholder="铺位名称"/>
                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 

            </form>
        </div>
        <!-- 将选中数据的值更新到#mystatus-->        
        <script type="text/javascript">
            $(document).ready(function() {

                var jsonstr = <?php echo $jsonstr ?>;//json数据,无''
                //alert(jsonstr.mytype);
                $('#mytype').val(jsonstr.mytype);

            })

        </script>

        <?php
        if ('n' == $this->j['success']) {
            $this->showerrlist($this->j['errmsg']);
        } else {
            ?>
            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="40">ID</th>

                    <th width="">所属店铺</th>
                    <th>铺位</th>
                    <th>操作类型</th>
                    <th>操作人</th>
                    <th>商品信息</th>
                    <th>操作时间</th>


                </tr>

                <?php
                foreach ($list['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['placetitle'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['fullname'] . '</td>' . PHP_EOL;

                    echo '<td>';

                    $a_historygoods = json_decode($v['historygoods'], true);
                    if (is_array($a_historygoods)) {
                        foreach ($a_historygoods as $x) {

                            echo $x['title'] . '&nbsp;门 &nbsp; ';
                            echo '<img src="' . $x['preimg'] . '" width="30" /> &nbsp; ';
                            echo $x['goodstitle'];
                            echo '<br />';
                        }
                    }
                    echo '</td>';
                    echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
                ?>
            </table>


            <?php
            $this->pagelist($j['list']['total']);
        }
    }

}

$tp = new myclass();

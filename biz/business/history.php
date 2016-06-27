<?php
/* 商品管理 */
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');

/* 把数据给模板的数据全据变量j */

class myclass extends cls_template {

    function __construct() {
        require_once(ApiPath . '/biz/business/_history.php'); //访问接口
        require_once( syspath . '/biz/checkbiz.php' ); //检测权限

        $this->comid = $this->rid('comid');

        $j = & $GLOBALS['j'];

        $this->addcrumb($j['company']['title']); //crumb加上公司名

        $this->addcrumb('补换货记录');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
        }
    }

    /* 商家列表 */

    function mylist() {
        $j = & $GLOBALS['j'];
        $place = $j['place'];
        $list = $j['list'];
        $comid = $this->get('comid'); //接收上一页传的comid
        $placeid = $this->get('placeid');
        $mytype = $this->get('mytype');

        crumb($this->crumb);
        ?>
        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">

                <input type="hidden" name="comid" value="<?php echo $this->comid ?>" />
                &nbsp;时间段：
                <input type="hidden" name="act" id="act" value="" />
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;

                <select name="placeid" id="placeid">
                    <option value="" selected="selected">全部铺位</option>

                    <!--此处用了place节点，铺位调用铺位表的title-->
                    <?php
                    foreach ($place as $v) {

                        echo '<option value="' . $v['id'] . '">' . $v['title'] . '</option>';
                    }
                    ?>    

                </select>
                <select name="mytype" id="mytype">
                    <option value="" selected="selected">类型</option>
                    <option value="replenish">补货</option>
                    <option value="change">换货</option>
                </select>

                <input type="submit" value=" 搜索 " id="btnsearch">

            </form>
        </div>
        <div id="data_placeid" style="display:none"><?php echo $placeid ?></div><!--接受选中的数据-->
        <div id="data_mytype" style="display:none"><?php echo $mytype ?></div><!--接受选中的数据-->



        <script type="text/javascript">
            <!--
               $(document).ready(function() {
                $('#btnsearch').on('click', function() {
                    $('#act').val('');
                })
        //将选中数据的值更新到select      
                var placeid = $('#data_placeid').text();
                $('#placeid').val(placeid);
                var mytype = $('#data_mytype').text();
                $('#mytype').val(mytype);
                return true;


            })
            //-->
        </script>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">时间</th>
                <th width="*">铺位</th>
                <th width="240">柜门</th>
                <th width="*">类型</th>  
                <th width="60">操作人id</th>
                <th width="80">操作人昵称</th>

            </tr>

            <?php
            if (false != $list) {
                foreach ($list['rs'] as $v) {

                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '</td>' . PHP_EOL;
                    echo '<td>' . $v['building'] . '栋-' . $v['floor'] . '层-' . $v['placetitle'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['doortitles'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['fullname'] . '</td>' . PHP_EOL;

                    echo '</tr>' . PHP_EOL;
                }
            }
            ?>
        </table>
        <?php
         
        $this->pagelist($list['total']);
        if ('y' != $j['success']) {
            $this->showerrlist($j['errmsg']); //把错误信息打印出来
        }
       
    }

}

$tp = new myclass();

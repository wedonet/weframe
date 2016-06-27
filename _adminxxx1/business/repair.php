<?php
/* 店铺管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        require_once AdminApiPath . 'business' . DIRECTORY_SEPARATOR . '_repair.php'; //访问接口去
        require_once(adminpath.'checkpower.php'); //检测权限
        require_once( adminpath . 'checkpower.php' ); //检测权限
        $this->addcrumb('业务管理');
        $this->addcrumb('设备维修');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;

            case 'admin':
                $this->fname = 'formadmin';
                require_once( adminpath . 'main.php' ); //主模板			
                break;


            /* 下面几个不需要渲染 */
            case 'isrun':
            case 'unrun':
            case 'islock':
            case 'unlock':

            case 'nsave':
            case 'esave':
            case 'savepass':
            case 'del':

                break;
        }
    }

    /* 设备维修列表 */

    function mylist() {

        $j = & $GLOBALS['j'];

        $list = & $j['list'];
        $jsonstr = json_encode($j['search']); //将数组字符串化

        crumb($this->crumb);
        ?>


<div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 
                <input type="text" name="comname" value="<?php echo $j['search']['comname'] ?>" placeholder="店铺名称" />
               
                <input type="text"  name="deviceic" value="<?php echo $j['search']['deviceic'] ?>" placeholder="设备IC"/> 
                
                时间段：
                <input type="text" size="15" value="<?php echo $j['search']['date1'] ?>" id="date1" name="date1" class="hasDatepicker">
                至
                <input type="text" size="15" value="<?php echo $j['search']['date2'] ?>" id="date2" name="date2" class="hasDatepicker">
                &nbsp;
                <input type="hidden" name="act" id="act" value="" />
                
                <select name="mytype" id="mytype">
                    <option value="all">全部</option>
                    <option value="locker">门锁未打开</option>
                    <option value="heart">掉线</option>
                    <option value="trouble">机体倾斜</option>
                </select>

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 

            </form>
        </div>


        <div class="navoperate">
            <ul>
                <li><a id="" href="repairhistory.php">维修记录</a> &nbsp; </li>
            </ul>
        </div>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="30">ID</th>
                <th width="110">所属店铺</th>
                <th width="*">店铺地址</th>
                <th width="*">店铺电话</th>
                <th width="*">铺位</th>
                <th width="*">设备IC</th>
                <th width="*">故障位置</th>
                <th width="*">故障时间</th>
                <th width="*">类型</th>
                <th width="*">状态</th>
                <th width="100">操作</th>
            </tr>

            <?php
            if(is_array($list['rs'] ))
            {
            foreach ($list['rs'] as $v) {

                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['address'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['tel'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['building'] . '-' . $v['floor'] . '-' . $v['title'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['deviceic'] . '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;
                if ('0' == $v['door']) {
                    echo '整体';
                } 
                else {
                    echo $v['door'];
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;

                echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
                echo '<td width="60">';
                switch ($v['status']) {
                    case '0':
                        echo '等待维修';
                        break;
                    case '1':
                        echo '维修中';
                        break;
                    case 'new':
                        echo '';
                        break;
                }
                echo '</td>' . PHP_EOL;
                echo '<td>' . PHP_EOL;

                if ('1' != $v['status']) {
                    echo '<a href="?act=fix&deviceic=&amp;id=' . $v['id'] . '" title="维修中" class="confirmedit">维修中</a> &nbsp; ' . PHP_EOL;
                    
                }
                else{
                    if('0' == $v['door']){
                        echo '<a href="?act=finish&deviceic=&amp;id=' . $v['id'] . '" title="已修复" class="confirmedit">已修复</a> &nbsp; ' . PHP_EOL;
                    }  
                }
                echo ' </td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            }
            ?>

        </table>

 <!--将选中数据的值更新到#mystatus-->
        <script type="text/javascript">
            $(document).ready(function () {

                var jsonstr = <?php echo $jsonstr ?>;//json数据,无''
        //alert(jsonstr.mystatus);
                $('#mytype').val(jsonstr.mytype);//更新下拉选框值

            })

        </script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.confirmedit').j_confirmedit(function(json) {
                    /*设置成功刷新页面*/
                    if ('y' == json.success) {
                        document.location.reload();
                    }
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
        } 
        

        $this->pagelist($j['list']['total']);
    }
    
    function showerr(&$j) {
        foreach ($j['errmsg'] as $v) {
            echo $v;
        }
    }

}

$tp = new myclass();
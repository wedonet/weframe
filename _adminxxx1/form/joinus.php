<?php
/* 管理中心 - 广告招商模块 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        require_once AdminApiPath . 'form' . DIRECTORY_SEPARATOR . '_joinus.php'; //访问接口去

      require_once( adminpath . 'checkpower.php'); //检测权限

        $this->addcrumb('<a href="?">招商加盟</a>');



        switch ($this->act) {
            case '' :
                $this->fname = 'mylist'; //主内容区
                require_once(adminpath . 'main.php'); //主模板
                break;
           
        }
    }

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list']['rs'];
        $total = & $j['list']['total'];

        crumb($this->crumb);
        ?>
        

        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="50">ID</th>
                <th width="*">姓名</th>
                <th width="*">手机号</th>
                <th width="*">职业</th>
                <th width="450">留言</th>
                <th width="80">提交时间</th>
                
            </tr>

            <?php
            foreach ($list as $v) {
                echo '<tr class="j_parent">' . PHP_EOL;
                echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_name'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_mobile'] . '</td>' . PHP_EOL;
                echo '<td>' . $v['f_field'] . '</td>' . PHP_EOL;
                echo '<td style="word-break: break-all">' . $v['f_message'] . '</td>' . PHP_EOL;
                echo '<td>' . date('Y-m-d H:i:s', $v['stimeint']) . '&nbsp;</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
            }
            ?>
        </table>

   

        <?php
        $this->pagelist($total);
    }

    

   

    function showerr(&$j) {
        foreach ($j['errmsg'] as $v) {
            echo $v;
        }
    }

}

$tp = new myclass(); //调用类的实例
unset($tp);

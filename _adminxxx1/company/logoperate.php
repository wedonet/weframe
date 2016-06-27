<?php
/* 铺位管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        
        
        require_once(AdminApiPath . 'company/_logoperate.php');/* 提取数据 */

		$this->addcrumb('操作日志');

		$this->addcrumb('业务管理');	

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
        
        $list =& $j['userlist']['rs'];

        $this->addcrumb($j['company']['title']); //crumb加上公司名

        $data = & $j['data'];

        $comid = $this->get('comid');

        crumb($this->crumb);
        require_once('biztab.php'); /*商家业务选项卡*/
        
        ?>
        <p></p>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">ID</th>
                <th width="100">操作人ID</th>
                <th width="*">操作人</th>
                <th width="*">换货原因</th>
                <th width="*">柜门id</th> 
                <th width="*">操作时间</th>
                                
            </tr>

        <?php
        foreach ($data as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['uid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['unick'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['content'] . '</td>' . PHP_EOL; 
            echo '<td>' . $v['doorid'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['stimeint'] . '</td>' . PHP_EOL;

            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>

        <?php
        $this->pagelist($j['userlist']['total']);
       
    }
    
    
  
}

$tp = new myclass();//调用类的实例
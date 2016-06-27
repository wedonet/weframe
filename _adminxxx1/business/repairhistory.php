<?php
/* 店铺管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        require_once AdminApiPath . 'business' . DIRECTORY_SEPARATOR . '_repairhistory.php'; //访问接口去
        require_once(adminpath.'checkpower.php'); //检测权限
        $this->addcrumb('业务管理');
        $this->addcrumb('设备维修');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'creat':
                $this->fname = 'myform'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板

                break;
            case 'edit':
                $this->fname = 'formedit';
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


        crumb($this->crumb);
        ?>
       
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="110">所属店铺</th>
                <th width="200">店铺地址</th>
                <th width="*">店铺电话</th>
                <th width="*">铺位</th>
                <th width="*">故障位置</th>
                <th width="*">故障时间</th>
                <th width="*">修复时间</th>
                <th width="*">类型</th>
                <th width="*">状态</th>
               
            </tr>

        <?php
        
        foreach ($list['rs'] as $v) {
            echo '<tr class="j_parent">' . PHP_EOL;
            echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['address'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['tel'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['building'] . '-' . $v['floor'] . '-' . $v['title'] . '</td>' . PHP_EOL;
            echo '<td>' ;
            switch($v['door']){
                        case '0':
                            echo '整体';
                            break;
                        default:
                            echo $v['door'];
                            break;
                     } 
            
            '</td>' . PHP_EOL;
            echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
            echo '<td>' . $v['repairtime'] . '</td>' . PHP_EOL;
           echo '<td>' . $v['mytypename'] . '</td>' . PHP_EOL;
            echo '<td>' ;
            switch($v['status']){
						case '0':
							echo '自动修复';
							break;
						case '1':
							echo '人工修复';
							break;
					 } 
            
             '</td>' . PHP_EOL;           
            
            echo '</tr>' . PHP_EOL;
        }
        ?>
        </table>


        <?php
        $this->pagelist($j['list']['total']);
    }

    

    

}

$tp = new myclass();
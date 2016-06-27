<?php
/* 数据维护 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 什么情况下必须返回json格式 */

        $jsonact = array('json'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }

        require_once AdminApiPath . 'data' . DIRECTORY_SEPARATOR . '_index.php'; //访问接口去
        require_once(adminpath . 'checkpower.php'); //检测权限


        $this->addcrumb('数据维护');

        switch ($this->act) {
            case '':
                $this->fname = 'pagemain'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'refreshordergoods':
                $this->fname = 'refreshordergoods'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
        }
    }

    /* 商家列表 */

    function pagemain() {
        $j = & $GLOBALS['j'];

        crumb($this->crumb);
        ?>

        <div class="navoperate">
            <ul>
                <li><a href="?act=creat">数据维护</a></li>
            </ul>
        </div>        

        <p><a href="?act=refreshordergoods">刷新定单商品</a></p>
        <?php
    }

    function refreshordergoods(){
        crumb($this->crumb);
        
        $myapi = new myapi(); //建立类的实例
        
        $myapi->refreshordergoods();
        
        echo '<div>刷新成功！</div>';
        
        unset($myapi); //释放类占用的资源
    }
}

$tp = new myclass();

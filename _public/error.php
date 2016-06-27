<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {


        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->index(); //主内容区

                break;
        }
    }

    function index() {
       
        $j = & $GLOBALS['j'];
        $j['headtitle'] = '抱歉，出错啦';
        require_once(syspath . 'bying/public/header.php');
        ?>

        <div class="title">出错啦</div>
        <a class="titlebg" href="javascript:void(0);" onclick="javascript :history.go(-1);"></a>
        <a href="tel:4006-992-996" class="contactus"></a> 
        <div class="main">
            <div class="error" id="">这里是错误信息！错误信息！信息！息！这里是错误信息！错误信息！信息！息！这里是错误信息！错误信息！信息！息！</div>
           
        </div>

        <?php
        require_once(syspath . 'bying/public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>
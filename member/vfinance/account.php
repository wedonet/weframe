<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

function headplus() {
    echo '<link rel="stylesheet" href="/_css/form.css" type="text/css"/>';//调用自己的css
    
}

class myclass extends cls_template {

    function __construct() {
        parent::__construct();//执行上级的构造函数
        
        /* 跟据act确定输出格式 */
        $jsonact = array();
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }
        
        require_once(ApiPath . 'member/vfinance/_account.php'); //访问接口去
        require_once('/../checkmember.php');//检测权限
 

        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区

                break;
        }
    }

    function pagemain() {
        $j = & $GLOBALS['j'];
        
       
//        $j['headtitle'] = '我的赠款';
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">我的赠款</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            <div class="block">
                <div class="vfinance"><img src="/../_images/vfinance.png"  alt=""/></div>
                <div class="v_num">
                    <span>余额</span>
                    <span class="v_price">¥<?php echo sprintf("%.2f",$j['vmoney']/100)?></span>
                </div> 
                <a href="list.php" class="f_btn">查看明细</a> 
            </div>  
                       
        </div>
        <script type="text/javascript">//当ul为空时显示无记录提醒
            if($(".j_ul li").length<2){
                $('.emptytext').show();
            }
            </script>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>
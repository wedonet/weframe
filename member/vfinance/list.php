<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

function headplus() {
    echo '<link rel="stylesheet" href="/_css/member.css" type="text/css"/>';
}

class myclass extends cls_template {

    function __construct() {
        parent::__construct();//执行上级的构造函数
        
        /* 跟据act确定输出格式 */
        $jsonact = array();
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }
        
        require_once(ApiPath . 'member/vfinance/_list.php'); //访问接口去
        require_once('/../checkmember.php');//检测权限
 

        switch ($this->act) {
            case'':
                $this->pagemain(); //主内容区

                break;
        }
    }

    function pagemain() {
        $j = & $GLOBALS['j'];
        
        $list = & $j['list'];
//       print_r($j);die;
        
        $j['headtitle'] = '';
        
        require_once(syspath . '_public/header.php');
        ?>

        <div class="title">收支明细</div>
        <a class="titlebg" id='carback' href="javascript:history.go(-1)"></a>

        <div class="main">
            
            <div class="myul">
                <ul class="accountul j_ul">
                    <li class="colorfont"><span class="myspan01 linehight1">时间</span><span class="myspan02">交易(元)</span><span class="myspan03">备注</span></li>
                    <?php
                    foreach ($j['list'] as $v) {
                    echo '<li> '. PHP_EOL;
                    echo '<span class="myspan01">' . date('Y-m-d H:i:s', $v['stimeint']) . '</span>' .PHP_EOL;
                    
                    
                    if($v['myvalue']>0){
                        echo '<span class="myspan02 green">+' . sprintf("%.2f",$v['myvalue']/100) . '</span>' .PHP_EOL;
                    }
                    if($v['myvalueout']>0){
                        echo '<span class="myspan02 dark" style="display:inline;" class="">-' . sprintf("%.2f",$v['myvalueout']/100) . '</span>' .PHP_EOL;
                        
                    }
                    
                    if(''!==$v['other']){
                    echo '<span class="myspan03">' . $v['other'] . '</span>' .PHP_EOL;
                    }
                    else{
                    echo '<span class="myspan03">无</span>' .PHP_EOL;
                    }
                    echo '</li>'. PHP_EOL;
                    
                    
                    }
                    ?>
                    
                </ul>
                <div class="emptytext" style="display:none">当前无记录！</div>
            </div>
        </div>
        <script type="text/javascript">//当ul为空时显示无记录提醒
            if($(".j_ul li").length<2){
                $('.emptytext').show();
                $('.colorfont').hide();
            }
        </script>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>
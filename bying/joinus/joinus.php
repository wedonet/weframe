<?php
require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {

        require_once(ApiPath . 'bying/joinus/_joinus.php'); //访问接口去

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->index(); //主内容区

                break;
        }
    }

    function index() {
        $j = & $GLOBALS['j'];

        //$j['headtitle'] = '招商加盟';
      // $doorid=$j['doorid'];
        $doorid = $this->get('doorid');
  
        require_once(syspath . '_public/header.php');


        /* 立即支付的链接地址 */
       // $hrefpay = 'order.php?doorid=' . $doorid;
        ?>
        <div class="main">      
            <div class="title">招商加盟</div> 
<!--            <a class="titlebg" href="javascript:history.back(-1)"></a>-->
   <a class="titlebg" href="../index.php?d=<?php echo $doorid ?>"></a>
           
            <div class="con_box">
                
                <a href="form.php?doorid=<?php echo $doorid ?>"><img src="joinindex.jpg" alt=""/></a>
            </div>
        
        </div>

        <?php
        require_once(syspath . '_public/footer.php');
    }

}

$tp = new myclass();
unset($tp);
?>
<?php
require_once(_DIR_ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');

class myclass extends cls_template {

    function __construct() {

        require_once(ApiPath . 'bying/_infor.php'); //访问接口去

        $this->act = $this->ract();

        switch ($this->act) {
            case'':
                $this->index(); //主内容区

                break;
        }
    }

    function index() {
$detail = $GLOBALS['j']['detail'];
$j = & $GLOBALS['j'];
$j['headtitle'] = '支付提醒';
        require_once(syspath . '_public/header.php');
        ?>


       <div class="title">支付成功</div>
       <a class="titlebg" href="javascript:void(0);" onclick="javascript :history.go(-1);"></a>
        <div class="main">
            <div class="checked clearfix">
            <div class="paysuccess" id="paysuc" style="display:">
            	<div class="suctitboder"><div class="suctit">恭喜您购买商品成功</div></div>
                <div class="suctxtboder">
                    <div class="suctxt">
                        <span class="red">由于机器故障，柜门未打开，<br/></span>
                        请您拨打酒店电话：<a href="tel:"><span class="font18">022-123456</span></a>，<br/>
                        商品将为您送至您的房间，<br/>
                        给您带来的不便，我们深表歉意。
                    </div>
           	    </div>
                <div class="clearfix"></div>
            </div>
            <div class="payno" id="payno" style="display:none;">
            	<div class="suctitboder"><div class="suctit1"><!--<img class="inc" src="/_images/inc.png" alt="" />-->恭喜您购买商品成功</div></div>
                <div class="suctxtboder">
                    <div class="suctxt">
                    售货机柜门已打开，请您领取您的商品，<br/>
                    感谢您对神灯的支持，祝您生活愉快！
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="but111"><a href="index.php"><input type="button" name="goon" class="goon" value="继续购买"/></a></div>
        </div>
        <script type="text/javascript">
        
			<?php
			if('close' == $v['doorstatus']){
				document.getElementById('paysuc').style.display=='block';
				document.getElementById('payno').style.display=='none';
				}
				else{
			    document.getElementById('paysuc').style.display=='none';
				document.getElementById('payno').style.display=='block';
					}
			?>
        </script>
        
        <?php
        require_once(syspath . '_public/footer.php');
    }
}

$tp = new myclass();
unset($tp);
?>
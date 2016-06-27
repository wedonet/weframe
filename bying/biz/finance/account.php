<?php

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(syspath . '/biz/public.php');
/*先去要数据
 * 再写模板
 */

/*把数据给模板的数据全据变量j*/

class myclass extends cls_template{
    
    function __construct() {
         require_once(ApiPath.'/biz/finance/_account.php');	//访问接口
         require_once( syspath . '/biz/checkbiz.php' ); //检测权限

         $this->addcrumb('财务统计');
	$this->comid = $this->rid('comid');	
        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'myform'; //主内容区
                require_once( syspath . '/biz/main.php' ); //主模板
                break;
            
          
        }
    }  
    
    
    function myform() {

        $j = & $GLOBALS['j'];
       $data = $j['data'];
       
        crumb($this->crumb);
        ?>
        <table class="table1" cellspacing="1" >

                <td>可用余额：</td>
                <td><?php echo sprintf("%.2f",$j['data']['acanuse']/100) ?>元</td>

                <tr>
                    <td>出款总额：</td>
                    <td><?php echo sprintf("%.2f",$j['data']['aout']/100) ?>元</td>
                </tr>

                <tr>
                    <td>入款总额：</td>
                    <td><?php echo sprintf("%.2f",$j['data']['ain']/100) ?>元</td>
                </tr>
             
 </table>
       

<?php 

}
  
}

$tp = new myclass();
unset($tp);
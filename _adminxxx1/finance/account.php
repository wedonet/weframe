<?php
/* 用户出款 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        require_once AdminApiPath . 'finance' . DIRECTORY_SEPARATOR . '_account.php'; //访问接口去
        require_once(adminpath.'checkpower.php'); //检测权限
        $this->addcrumb('资金统计');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'myform'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
    
        }
	}


	function myform(){
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

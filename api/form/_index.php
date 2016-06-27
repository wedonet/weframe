<?php

/* 补货首页 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'form/_main.php';

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }


        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
        }
    }

    /**/

    function pagemain() {
        $uid =$this->main->user['id'];
        /*提取调查*/
       // $sql = 'select * from `'.sh.'_form` where 1 ';
       //  $sql .= ' and mystatus="doing" ';  所有状态的问卷均显示
       // $sql .= ' order by mycount desc ';
		
        $sql = " select case mystatus when 'doing' then 1";
        $sql .= " when 'done' then 2";
        $sql .= " when 'over' then 3 else 4";
        $sql .= ' end as mystatus1, mystatus, mycount, myvalue,title,ic,id,readme';
        $sql .= ' from `' . sh . '_form`';
        $sql .= " where mystatus!='new'";
        $sql .= ' order by mystatus1 asc, myvalue desc';
         
	  
	    //$this->j['list'] = $this->pdo->fetchAll($sql);
	   
	     //查询问卷列表
		$result=$this->pdo->fetchAll($sql);  //获得问卷列表数据
		
		for($i=0;$i<count($result);$i++){  //遍历获得result数据
			// 根据当前用户id 和遍历的问卷id 判断 该用户是否参加过
			
			$sql='select formid from `'. sh . '_formdolist` where uid="'.$uid.'" and  formid="'.$result[$i]['id'].'"';
			
			$doing=$this->pdo->fetchAll($sql);
			//判断是否用户填写过问卷
			if(count($doing)>0){
				//如果填写过给当前查询到的问卷数据result添加一个isdoing字段以便前端添加样式
				$result[$i]['isdoing']=1;
			}
			else{
				$result[$i]['isdoing']=0;
				}
		
		}
		
		//把重新获得的$result 给前台
		
		  $this->j['list'] = $result;

        
	}
}

    
	



$myapi = new myapi();
unset($myapi);

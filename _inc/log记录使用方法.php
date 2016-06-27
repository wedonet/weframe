<?php
exit();

//请看下面




//开启日志记录相关配置  可以放在config.php 里配置

$GLOBALS['start_log']=$config['start_log']=true;//是否开启日志记录
$config['mongodbhost']='192.168.0.22';//mongo主机
$config['mongodbport']="27017";//mongo端口
$config['log_type']='USER|ERROR|INFO|ERR|FETCH|INSERT|DOSQL|UPDATE|DEL|EXECUTE';
//需要记录的日志类型 不再这里配置的不予与记录
if($config['start_log']){
    require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'log.php');
    require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'ClsMongoDb.php');
    try{
         $GLOBALS['mongodb']=new ClsMongoDb(['host'=>$config['mongodbhost'],'port'=>$config['mongodbport']]);
    } catch (MongoException  $ex) {
    //初始化mongodb 失败 有三种可以选择
    // $GLOBALS['start_log']=FALSE;       //无法连接mongo则不进行日志记录
        
    //或者使用文本临时记录
    $GLOBALS['log']=new Log(["do_action"=>'do_action_by_txt',"log_name" =>"log.log",'log_dir'=>__DIR__."/_cache/",'log_type'=>$config['log_type']]);
    $GLOBALS['log']->add("ERROR","无法连接mongodb", __FILE__, __LINE__);
        
        
    //或者抛出异常  die()掉服务
    //throw new MongoException ("无法连接mongo");die();
    }
    
    if(!isset($GLOBALS['log'])){//如果mongo初始化正常则用mongo记录方式初始化日志记录
        $GLOBALS['log']=new Log(["do_action"=>'do_action_by_mongodb',"mongodbname" =>"shengdenglog",'log_type'=>$config['log_type']]);
    }    
}

/*----------------------------------------------------------------------*/

//在需要插入记录信息的地方 加入现的语句

if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){//检测配置里是否开启记录
	//参数一  全部大写 记录信息类型 必须在配置里配置 否则不予与记录
	//参数二 sql语句 
	//参数三 sql语句附带的参数
	//参数四 参数五当前文件名和行号 一般默认即可
    $GLOBALS['log']->autosql('FETCH',$sql,$para,__FILE__,__LINE__);	
	
	//参数一  全部大写 记录信息类型 必须在配置里配置 否则不予与记录
	//参数二 要记录的信息
	//参数三 参数四 当前文件名和行号 一般默认即可
    $GLOBALS['log']->add('INFO',"查询成功",__FILE__,__LINE__);

	
    //这俩任选其一  做记录                
}
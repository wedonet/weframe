<?php

//
//使用方式：
//$log=new Log();
//  可用数组传入参数配置  默认的可以不传参数
//  $config=[
//  'do_action' =>"do_action_by_txt", //（日志处理方式 默认用文本记录）  
//  'log_name'=>"log.log" ,//  日志文件名称 按天记录可 设置为 date("Ymd")."log.log"
//  'log_dir'=>"__DIR__" ,//  //日志文件目录
//  'log_type'=>"INFO|FETCH", //需要记录的日志文件类型
//  ]
//  
//$log->autosql($type,$sql,$para);可以记录sql
//$log->add($type, $msg, __FILE__, __LINE__);//可记录自定义信息

class Log {
    private $msglog; //暂存的日志信息    
    private $sqllog; //暂存的日志信息
//    private static $mongodb; //暂存mongodb操作类
    public $do_action = "do_action_by_txt"; //处理日志方式
    public $log_name = "log.log"; //日志名称
    public $log_dir = __DIR__; //日志存放位置
    public $mongodbname = "mylog"; //日志存放位置
    public $log_type = "USER|ERROR|INFO|ERR|FETCH|INSERT|DOSQL|UPDATE|DEL|EXECUTE";  //记录的日志类型 多个用|分割 不再这里配置的不记录

    public function __construct($config = []) {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                if (!empty($value)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function autosql($type = 'SELECT', $sql = '', $para = null, $file = '', $line = '') {
        $log_type = explode("|", $this->log_type);
        if (!in_array($type, $log_type)) {//如果日志方式在配置的格式中则记录 否则不记录
            return;
        }
        $data['ip'] = $this->getip();
        $data['time'] = date("Y-m-d H:i:s");
         $data['inttime'] =  time(); //时间
        $data['type'] = $type;
        if (!empty($_SESSION['selleruser'])) {
            $data['uid'] = $_SESSION['selleruser']['id'];
            $data['u_name'] = $_SESSION['selleruser']['u_name'];
        } else {
            $data['uid'] = 0;
            $data['u_name'] = 'unkown';
        }
        $data['url'] = $_SERVER['URL'];
        $data['query_string'] = $_SERVER['QUERY_STRING'];
        $data['sql'] = $sql;
        $data['para'] = $para;
        if (isset($_POST)) {
            $data['psotdata'] = $_POST;
        }
        $data['file'] = $file;
        $data['line'] = $line;
        $this->sqllog[] = $data;
    }

    public function add($type = 'INFO', $msg = '', $file = __FILE__, $line = __LINE__) {
//        在普通程序里可以增加自定义的 日志
        $log_type = explode("|", $this->log_type);
        if (!in_array($type, $log_type)) {//如果日志方式在配置的格式中则记录 否则不记录
            return;
        }
        $data['ip'] = $this->getip(); //ip
        $data['time'] = date("Y-m-d H:i:s"); //时间
        $data['inttime'] =  time(); //时间
        if (!empty($_SESSION['selleruser'])) {
            $data['uid'] = $_SESSION['selleruser']['id'];
            $data['u_name'] = $_SESSION['selleruser']['u_name'];
        } else {
            $data['uid'] = 0;
            $data['u_name'] = 'unkown';
        } //如果登录 获取登录信息
        $data['url'] = $_SERVER['URL']; //当前url
        $data['query_string'] = $_SERVER['QUERY_STRING']; //执行参数
        $data['type'] = $type;
        $data['msg'] = $msg;
        if (isset($_POST)) {
            $data['psotdata'] = $_POST;
        }
        $data['file'] = $file;
        $data['line'] = $line;
        $this->msglog[] = $data;
    }

    /**
     * 需要传入参数 @do_action 为 do_action_by_mongodb
     * 
     * * */
    public function do_action_by_mongodb() {
        if (empty($GLOBALS['mongodb'])) {
            return;
        }
        $mongo = & $GLOBALS['mongodb'];
        $mongo->selectDB($this->mongodbname);
        if (!empty($this->sqllog)) {
            foreach ($this->sqllog as $v) {
                $mongo->insert('sqllog', $v);
            }            
        }  
        if (!empty($this->msglog)) {
            foreach ($this->msglog as $v) {
                $mongo->insert('msglog', $v);
            }            
        }  
 
    }

    /**
     * 需要传入参数 @do_action 为 do_action_by_txt  啥也不传 默认就是这个
     *              @log_name = "log.log";记录文件名
     *              @log_dir = __DIR__; //文本记录路径  默认当前目录  
     * 
     * * */
    public function do_action_by_txt() {
        $str = '';
        if (!empty($this->sqllog)) {
            foreach ($this->sqllog as $v) {
                $str.=json_encode($v, JSON_UNESCAPED_UNICODE) . "\r\n";
            }
            file_put_contents($this->log_dir . DIRECTORY_SEPARATOR . 'sql_' . $this->log_name, $str, FILE_APPEND);
        }
        $str = "";
        if (!empty($this->msglog)) {
            foreach ($this->msglog as $v) {
                $str.=json_encode($v, JSON_UNESCAPED_UNICODE) . "\r\n";
            }
            file_put_contents($this->log_dir . DIRECTORY_SEPARATOR . 'msg_' . $this->log_name, $str, FILE_APPEND);
        }
    }

    public function showlog($dir = '') {//数据太多时 会超内存报错  待优化
//        if (empty($dir)) {
//            $dir = $this->log_dir;
//        }
//        $msgarray = $sqlarray = [];
//        $logfile = glob($dir . "/*.log");
//        foreach ($logfile as $value) {
//            $data = file_get_contents($value);
//            $datas = explode("\r\n", $data);
//            if (strpos($value, "sql_") !== FALSE) {
//                $sqlarray = array_merge($sqlarray, $datas);
//            } else {
//                $msgarray = array_merge($msgarray, $datas);
//            }
//        }
//        echo "\r\n<br/>sql日志信息<br/>\r\n<pre>";
//        foreach ($sqlarray as $value) {
//            print_r(json_decode($value));
//        }
//        echo "\r\n<br/>日志信息<br/>\r\n<pre>";
//        foreach ($msgarray as $value) {
//            print_r(json_decode($value));
//        }

        //查看日志功能  待扩展
    }

    //程序运行结束时  集中处理记录的信息
    public function __destruct() {
        if (empty($this->do_action)) {
            return;
        }
        if(empty($this->msglog) && empty($this->sqllog)){
            return ;
        }
        if (method_exists($this, $this->do_action)) {
            call_user_func([$this, $this->do_action]);
        }
    }

    private function getip() {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);
        return $cip;
    }

}

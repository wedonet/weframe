<?php

/**
 * socket 通信类
 * @author by Xuzhongjian
 * @date 2015.12.9
 */
class cls_socket {

    private $ip;
    private $port;
    private $socket;

    function __construct($ip, $port) {
        $this->port = $port;
        $this->ip = $ip;
        //return $this->connect();
    }

    function connect() {
     
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    
        if ($this->socket < 0) {
         
            return "socket_create() failed: reason: " . socket_strerror($this->socket);
        }
        $connect = @socket_connect($this->socket, $this->ip, $this->port);
      if ($connect != 1) {
       // if ($connect < 0) {
       
            return "socket_connect() failed:Reason: ($connect) " . socket_strerror($connect);
        }
        return TRUE;
    }

    public function write($in) {
      
       
        if (!socket_write($this->socket, $in)) {
          
            return "socket_write() failed: reason: " . socket_strerror($this->socket);
        }
         
        return TRUE;
    }

    /*
     * 接收返回信息，$length长度
     */

    public function read($length = 8192) {
        while ($out = socket_read($this->socket, $length)) {
            return $out;
        }
        return false;
    }

    /*
     * 亮灯编码用方法，传入参数格式为1,2,3,4,5
     */

    private function getlamp($lamp) {
        $lamps = explode(',', $lamp);
        $lamp_str = '';
        foreach ($lamps as $key => $value) {
            $one = '';
            if ($value >= 10) {
                $one = '\x' . strtoupper(dechex($value));
                eval('$one = "' . $one . '";');
                $lamp_str .=$one;
            } else {
                $one = '\x0' . ($value);
                eval('$one = "' . $one . '";');
                $lamp_str .=$one;
            }
        }
        return $lamp_str;
    }

    /*
     * 设备编码用方法，传入参数为设备编号
     */

    private function getdevice($devicenum) {
        $len = strlen($devicenum);
        $bit = '';
        for ($i = 0; $i < $len; $i++) {
            $num = '\x' . ($devicenum[$i]);
            eval('$num = "' . $num . '";');
            //$num = chr(hexdec($num));
            $bit .=$num;
        }
        return $bit;
    }

    public function getsendstr($devicenum, $lamp) {
        $str = "\xA9"; //开始符

        $str .= $this->getdevice($devicenum);
        $str .= $this->getlamp($lamp);

        $str.=$this->getjy($devicenum, $lamp);
        $str .="\x9A"; //结束符
       // print_r($str);
        return $str;
        
    }

    function getjy($num, $lamp) {
        $len = strlen($num);
        $bit = 0;
        for ($i = 0; $i < $len; $i++) {
            $bit+= hexdec($num[$i]);//16进制转10进制
        }
        $lamps = explode(',', $lamp);
        foreach ($lamps as $key => $value) {
            if ($value > 9) {
                $bit+=$value;
            } else {
                $bit+=hexdec($value);
            }
        }
      // print_r($bit);
        //bit不会小于10
        
        $num = '\x' . strtoupper(dechex($bit));//dechex10进制转16进制
       // print_r($num);die;
        eval('$num = "' . $num . '";');
        $str = $num;
       // print_r($str);die;
        //$num1 = '\x' . strtoupper(dechex($bit * 2));
        //eval('$num1 = "' . $num1 . '";');
        //$str.=$num1;
        //print_r($str);die;
        return $str;
    }

    public function getsendstrs($devicenum) {
        $str = "\x56"; //开始符
        $str .= $this->getdevice($devicenum);
        $str .="\x65"; //结束符
        return $str;
    }

    public function close() {
        socket_close($this->socket);
    }

    /* function __destruct(){
      socket_close($this->socket);
      } */
}

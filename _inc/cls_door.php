<?php

/* 柜门处理 */
require_once syspath . '_inc/cls_socket.php';

class cls_door {
    /* 开门
     * $a_doors : 门号数组 {1,2,3}
     */

//$rs['deviceid']传进格式字符串
//$rs['deviceic']传进格式字符串
//$rs['doorid']传进格式字符串1,2,3
//$rs['doortitle']传进格式数组array(1,2,3)   
    function __construct($rs,$fg=0) {

        $this->main = & $GLOBALS['main'];
        $this->pdo = & $GLOBALS['pdo'];
        $this->errmsg = & $GLOBALS['errmsg'];
        if (isset($rs['deviceid'])) {
            $deviceid = $rs['deviceid'];
        } else {
            $deviceid = '';
        }
        if (isset($rs['deviceic'])) {
            $deviceic = $rs['deviceic'];
        } else {
            $deviceic = '';
        }
        if (isset($rs['doorid'])) {
            $doorid = $rs['doorid'];
        } else {
            $doorid = '';
        }
        if (isset($rs['doortitle'])) {
            $doortitle = $rs['doortitle'];
        } else {
            $doortitle = '';
        }

        if ('' == $deviceid && '' == $deviceic) {
            $this->errmsg[] = '请输入设备id或设备ic';
            return;
        }

        if ('' == $doorid && '' == $doortitle) {
            $this->errmsg[] = '请输入门id';
            return;
        }

        if ('' != $deviceid) {
            $sql = 'select ic from ' . sh . '_device where id=:id';
            $query[':id'] = $deviceid;
            $result = $this->pdo->fetchOne($sql, $query);
            $deviceic = $result['ic'];
            if ('' == $deviceic) {
                $this->errmsg[] = '输入的id号无效';
                return;
            }
        }


        if ('' != $doorid) {
            unset($sql);
            $sql = 'select title from ' . sh . '_door where id in(' . $doorid . ')';

            $result = $this->pdo->fetchAll($sql);

            foreach ($result as $v) {
                $doortitle[] = $v['title'];
            }

            if (0 == count($doortitle)) {
                $this->errmsg[] = '输入的门号无效';
                return;
            }
        }

        //$deviceic = 'C89346404765';
        //$doortitle= array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
        // $doortitle = array(18,19);

        $this->opendoor($deviceic, $doortitle,$fg);
    }

    /* 开门，连不上柜门机，向全局errmsg报错 */

    function opendoor($deviceic, $a_doors,$fg) {
// $ip = '101.200.123.1'; //神灯单片机服务器地址
        if (!isset($GLOBALS['config']['serverip'])) {
            $ip = '101.200.123.1';
        } else {
            $ip = $GLOBALS['config']['serverip'];
        }

        //$port = '50601';

        if (1 === ($this->main->cache->get(CacheName . $deviceic))) {
            $this->errmsg[] = '正在打开柜门，请稍后操作';
            //  print_r('11111111');
            return false;
        } else {
            // print_r('22222222222');
            if("1"==$fg)
            {
                 $this->main->cache->save(CacheName . $deviceic, 1, 1);
            }
            else
            {
            $this->main->cache->save(CacheName . $deviceic, 1, 5);
            }
        }

        /* 发开门指令 */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://' . $ip . ':1337/index.html/' . $deviceic . '/' . join(',', $a_doors));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出  
        $result = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($result, true);

        /* 判断返回格式是否正确 */
        if (is_null($json)) {
            $this->errmsg[] = '神灯服务器连接失败';
            return false;
        }

        if ('n' == $json['success']) {
            //$this->errmsg[] = $json['errmsg'];
            //$this->errmsg[] = '网络不畅，请联系前台购买';
            return false;
        }

        return true;
    }

    function sendopen($c_socket, $list, $deviceic) {
        $len = strlen($list);
        $list = $list . substr(0, $len - 1);
        $list = trim($list, ',');

        // echo 'opendoor:'.$list.'<br/>';
        $send = $c_socket->getsendstr($deviceic, $list);
        if ($c_socket->write($send)) {

            $this->j['success'] .= 'y';
            $this->j['idlist'] .= $list . ',';
        } else {
            $this->j['success'] = 'n';
        }
    }

}

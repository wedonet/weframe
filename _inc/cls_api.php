<?php

class cls_api {

    function __construct() {
        $this->pdo = & $GLOBALS['pdo'];
        $this->main = & $GLOBALS['main'];

        $this->j = & $GLOBALS['j'];
        $this->j['success'] = 'y'; //默认是成功的

        $this->act = $this->main->ract();

        /* 用户信息 */
        $this->j['user'] = $this->main->user;

        $this->j['errcode'] = 1000; //错误编码，正常返回0，默认没有权限，在接口中重写权限

        $this->j['company'] = $this->main->company;

        //$this->j['errcode'] = 0; //默认没有错误
        //$this->j['com'] = $this->main['com'];

        /* 调取一些临时数据 */
        require_once(ApiPath . '_tempvalue.php' );
    }

    function output() {
        $this->j['server']['runtime'] = $this->main->runtime();
        $this->j['server']['sqlquerynum'] = $this->pdo->sqlquerynum;

        if (isset($_GET['outtype']) OR isset($_POST['outtype'])) {
            echo json_encode($this->j);
        }
    }

    /* 有错误返回false,没有时返回true */

    function ckerr($errmsg = null, $errinput = null) {
        if (null != $errmsg) {
            
            /*清空全局错误信息*/
            unset($GLOBALS['errmsg']);
            
            if (is_numeric($errmsg)) {
                $GLOBALS['errmsg'][] = $GLOBALS['config']['err'][$errmsg];
            } else {
                $GLOBALS['errmsg'][] = $errmsg;
            }
        }
        if (null != $errinput)
            $GLOBALS['errinput'][] = $errinput;



        if (count($GLOBALS['errmsg']) > 0) {

            $this->j['success'] = 'n';
            $this->j['errmsg'] = & $GLOBALS['errmsg'];


            $this->j['errinput'] = join(',', $GLOBALS['errinput']);


            /* 如果输出json格式，到这就可以停止了 */
            if (isset($_GET['outtype']) OR isset($_POST['outtype'])) {
                $this->output();
                die;
            } else {
                return false;
            }
        } else {

            return true;
        }
    }

    /* 向 j 添加err信息 */

    function returnerr($errmsg = null, $errinput = null) {

        if (null != $errmsg) {
            if (is_numeric($errmsg)) {
                $GLOBALS['errmsg'][] = $GLOBALS['config']['err'][$errmsg];
            } else {
                $GLOBALS['errmsg'][] = $errmsg;
            }
        }
        if (null != $errinput)
            $GLOBALS['errinput'][] = $errinput;



        if (count($GLOBALS['errmsg']) > 0) {
            $this->j['success'] = 'n';
            $this->j['errmsg'] = & $GLOBALS['errmsg'];


            $this->j['errinput'] = join(',', $GLOBALS['errinput']);

            return true;
        } else {

            return false;
        }
    }

    /* 加上用户信息 */


    /* 加上公司信息 */



    /* 店铺信息 */

    function __destruct() {
        if (isset($_GET['isprint'])) {
            print_r($this->j);
            die;
        }
    }

    /* 哪据身份检测权限
     * 
     * if outtype=json 返回json格式
     * 接口不管跳转问题
     * $user 是用户数组
     * user必须有 u_gic,u_roleic
     */

    function checkpower($user, $u_nick, $u_gic, $u_roleic = '') {
        $ihaspower = false;

        if ('' == $u_roleic) {
            if ($user['u_gic'] == $u_gic) {
                $ihaspower = true;
            }
        } else {
            if ($user['u_gic'] == $u_gic AND $user['u_roleic'] == $u_roleic) {
                $ihaspower = true;
            }
        }

        /* 没有权限跟据要求返回的格式做提示 */
        if (!$ihaspower) {
            $this->j['success'] = 'n';
            $this->j['errmsg'][] = '没有权限';
            $this->j['errcode'] = 1000;

            $this->output(); //

            return false;
        } else {
            return true;
        }
    }

		/*把接收到的参数存进日志*/
    function paralog() {
        $s = '';
        $s .= 'GetPara' . PHP_EOL;
        if (isset($_GET)) {
            foreach ($_GET as $k => $v) {
                $s .= ( $k . ' = ' . $v ) . PHP_EOL;
            }
        }

        if (isset($_POST)) {
            foreach ($_POST as $k => $v) {
                $s .= ( $k . ' = ' . $v ) . PHP_EOL;
            }
        }
        $fname = syspath . '_temp/' . str_replace('/', '_',  $_SERVER['PHP_SELF']) . '_' . time() . '.txt';

        //echo $fname;
        $this->main->write_file($fname, $s);
    }

}

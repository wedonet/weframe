<?php

class cls_template {

    public $headtitle;
    public $headkeywords;
    public $headdescription;
    public $crumb;
    public $isshow = false;
    public $fname = ''; //main function name

    function __construct() {
        $this->j = & $GLOBALS['j'];
        $this->main = & $GLOBALS['main'];
        $this->act = $this->main->ract();

        if (isset($_GET['isprint']) OR isset($_POST['isprint'])) {
            print_r($j);
            die;
        }
    }

// end func

    function headplus() {
        
    }

    /* 接收get或post的act */

    function ract($s = 'act') {
        if (isset($_POST[$s])) {
            return substr(trim($_POST[$s]), 0, 20);
        } else if (isset($_GET[$s])) {
            return substr(trim($_GET[$s]), 0, 20);
        } else {
            return null;
        }
    }

    function gact($s = 'act') {
        if (isset($_GET[$s])) {
            return substr(trim($_GET[$s]), 0, 20);
        } else {
            return null;
        }
    }

    function pact($s = 'act') {
        if (isset($_POST[$s])) {
            return substr(trim($_POST[$s]), 0, 20);
        } else {
            return null;
        }
    }

    function rid($s = 'id', $v = -1) {
        if (isset($_GET[$s])) {
            $x = Trim($_GET[$s]);
        } elseif (isset($_POST[$s])) {
            $x = Trim($_POST[$s]);
        } else {
            $x = $v;
        }

        if (strlen($x) > 20) {
            err(1022);
        }
        if (!is_numeric($x)) {
            return $v;
        } else {
            return floor($x);
        }
    }

    /**
     * 接收数字,没找到就认为是-1
     */
    function rqid($s = 'id', $v = -1) {
        if (isset($_GET[$s])) {
            $x = Trim($_GET[$s]);
        } else {
            $x = $v;
        }

        if (strlen($x) > 20) {
            showerr(1022);
        }
        if (!is_numeric($x)) {
            return $v;
        } else {
            return floor($x);
        }
    }

    /* 拉收post数字 */

    function rfid($s = 'id', $v = -1) {
        if (isset($_POST[$s])) {
            $x = Trim($_POST[$s]);
        } else {
            $x = $v;
        }

        if (strlen($x) > 20) {
            showerr(1022);
        }
        if (!is_numeric($x)) {
            return $v;
        } else {
            return floor($x);
        }
    }

    function addcrumb($s) {
        $this->crumb.=('<li>' . $s . '</li>' . PHP_EOL);
    }

    /* input value 
     * 有键时显示实际值，没有时显示空
     * $a : 数组
     * $k : 键
     */

    function ivalue(&$a, $k) {
        if (array_key_exists($k, $a)) {
            return $a[$k];
        } else {
            return '';
        }
    }

    function main() {

        if ('' !== $this->fname) {

            call_user_func_array(array($this, $this->fname), array());
        }
    }

    function get($id) {
        if (isset($_GET[$id])) {
            return $_GET[$id];
        } else {
            return '';
        }
    }

    /* 跟据1或0显示yes或no */

    function yesorno($v) {
        if (1 == $v) {
            return 'Yes';
        } else {
            return 'No';
        }
    }

    function shop($comid) {
        $sql = 'select title from `' . sh . '_com` where id=' . $comid;
        $rs = $GLOBALS['pdo']->fetchOne($sql);
        return $rs['title'];
    }

    function pagelist($total = 0, $maxpage = 0, $filename = null) {

        $this->getcurrentpage();


        $currentpage = $this->currentpage;


        if (0 == $total) {

//            $total = $this->totalrs;

            return false;
        }

        /* 取文件名 */
        if (null === $filename) {
            $a = explode('\\', __FILE__);
            $filename = end($a);
        }

        //这里没对$_GET[]进行检测
        //$_GET['page'] = '{$page}';
        $tget = $_GET;


        /* 如果有page这个参数 then 删之 */
        if (array_key_exists('page', $tget)) {
            unset($tget['page']);
        }




        $url = '?' . $this->arrtopara($tget);

        if (0 == $maxpage) {
            $maxpage = MaxPage;
        }


        /* 计算总页数 */
        if (0 === $total % $maxpage) {
            //$pagecount 在repm时生成
            $pagecount = $total / $maxpage;
        } else {
            $pagecount = floor($total / $maxpage) + 1;
        }


        /* 校正currentpage */
        if ($currentpage > $pagecount) {
            $currentpage = $pagecount;
        }

        /* 求pagelong */
        if ($currentpage < 6) {
            $pagelong = 11 - $currentpage;
        } else if (($pagecount - $currentpage) < 6) {
            $pagelong = 10 - ($pagecount - $currentpage);
        } else {
            $pagelong = 5;
        }

        //生成page字符串
        /* 只有一页时,只显示页数 */
        if ($pagecount < 2) {
            $s = '<li class="current">&nbsp;1&nbsp;</li>' . PHP_EOL;
        } else {
            for ($i = 1; $i < ($pagecount + 1); $i++) {
                /* 第一页不带参数 */
                if (1 == $i) {
                    $a[0] = $url;

                    if (1 == $currentpage) {
                        $p[0] = '<li class="current">&nbsp;1&nbsp;</li>' . PHP_EOL;
                    } else {
                        $p[0] = '<li><a href="' . $a[0] . '">&nbsp;1&nbsp;</a></li>' . PHP_EOL;
                    }
                } else {
                    if (($i < ($currentpage + $pagelong) AND $i > ($currentpage - $pagelong)) OR $i == $pagecount) {
                        $a[$i - 1] = $url;

                        //检测有没有?
                        if (FALSE !== stripos($a[$i - 1], '?')) { //没有时用? 有时则用&amp;
                            $a[$i - 1] .= '?page={$page}';
                        } else {
                            $a[$i - 1] .= '&amp;page={$page}';
                        }

                        if ($currentpage == $i) {
                            $p[$i - 1] = '<li class="current">&nbsp;' . $i . '&nbsp;</li>' . PHP_EOL; //给当前页加标记
                        } else {
                            $p[$i - 1] = '<li><a href="' . $this->addpara($url, 'page=' . ($i)) . '">&nbsp;' . $i . '&nbsp;</a></li>' . PHP_EOL;
                        }
                    }
                }
            }

            $s = join('', $p);
        }


        $strpage = '<div class="page">' . PHP_EOL;
        $strpage .= '<ul>' . PHP_EOL;
        $strpage .= '{$pre}' . PHP_EOL;
        $strpage .= '{$pagelist}' . PHP_EOL;
        $strpage .= '{$next}' . PHP_EOL;
        $strpage .= '</ul>' . PHP_EOL;
        $strpage .= '<div class="clear"></div>' . PHP_EOL;
        $strpage .= '</div>' . PHP_EOL;

        $strpage = str_replace('{$pagelist}', $s, $strpage); //替换翻页字符串

        /* Get 上一页和下一页链 */
        /* 上一页 */
        if ($currentpage > 1) {
            if (2 == $currentpage) { //第二页的上一页, 也就是第一页,不带page参数
                $prepage = '<li><a href="' . $url . '" class="pageleft"><</a>&nbsp;</li>' . PHP_EOL;
            } else {
                $prepage = '<li><a href="' . $this->addpara($url, 'page=' . ($currentpage - 1)) . '" class="pageleft"><</a>&nbsp;</li>' . PHP_EOL;
            }
        } else {
            //当前页就是第一页, 上一页没有链接了
            $prepage = '<li class="pageleft"><</li>' . PHP_EOL;
        }
        $strpage = str_replace('{$pre}', $prepage, $strpage);


        /* 下一页 */
        if ($currentpage < $pagecount) {
            $nextpage = '<li>&nbsp;<a href="' . $this->addpara($url, 'page=' . ($currentpage + 1)) . '" class="pageright">></a></li>' . PHP_EOL;
        } else {
            $nextpage = '<li class="pageright">></li>' . PHP_EOL;
        }
        $strpage = str_replace('{$next}', $nextpage, $strpage);


        echo $strpage;
    }

    /* 数组变参数 */

    function arrtopara($para) {
        $s = '';

        if (!is_array($para)) {
            return '';
        }

        foreach ($para as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $vv) {
                    $s.= '&amp;' . $k . '[]=' . $vv;
                    //$t[$k][] = $vv;
                }
            } else {
                $s .= '&amp;' . $k . '=' . $v;
            }
        }

        //不要开头的&amp;
        $s = substr($s, 5);

        return $s;
    }

    /* pagepara = 页的变量名称 */

    function getcurrentpage($pagepara = 'page') {
        //取传过来的页数
        $currentpage = $this->rqid($pagepara);

        if ($currentpage < 1) {
            $currentpage = 1;
        }

        $this->currentpage = $currentpage;
    }

    /* 向url追加参数 */

    function addpara($str, $para) {
        /* 没问号 */
        if (FALSE === stripos($str, '?')) {
            return $str . '?' . $para;
        } else {
            return $str . '&amp;' . $para;
        }
    }

//    function runtime(){   
//		return (round((microtime(true) - pagestarttime), 4));
//    }

    /* echo 错误信息 */
    function showerrlist($a) {
        echo '<div class="errlist">';
        echo '	<ul>';
        foreach ($a as $v) {
            echo '<li>';
            echo $v;
            echo '</li>';
        }
        echo '	</ul>';
        echo '</div>';
    }

}

function showerr($errmsg = null) {


    if (null != $errmsg) {
        if (is_numeric($errmsg)) {
            $GLOBALS['errmsg'][] = $GLOBALS['config']['err'][$errmsg];
        } else {
            $GLOBALS['errmsg'][] = $errmsg;
        }
    }



    if (count($GLOBALS['errmsg']) > 0) {
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<title>错误</title>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '</head>';
        echo '<body>';

        if (is_array($GLOBALS['errmsg'])) {
            foreach ($GLOBALS['errmsg'] as $v) {
                if (is_array($v)) {
                    foreach ($v as $x) {
                        echo $x;
                    }
                } else {
                    echo $v;
                }
            }
        } else {
            echo $GLOBALS['errmsg'];
        }
        echo '</body>';
        echo '</html>';


        die;
    }
}

<?php

class ClsMain {

    var $security;
    public $pdo;
    public $bfv;
    public $cache;
    public $page;
    public $currentpage; //当前页数
    public $mymaxpage; //当前sql每页记录数
    public $totalrs; //当前sql查询返回的记录数
    //public $user;
    //public $com;
    Public $ismember; //是否会员
    Public $ismaster; //是否管理员
    Public $istruemaster; //是否真正的管理员,即管理员在登录状态
    public $cachedgroup = false;
    public $scriptname;
    public $posttype = 'get'; //默认是get方式提交
    public $errmsg;
    public $errinput;

    function __construct() {
        $this->pdo = & $GLOBALS['pdo'];
        $this->cache = & $GLOBALS['cache'];


        $this->ismember = false;
        $this->ismaster = false; //前台登录后的管理员
        $this->istruemaster = false; //后台登录后
        //
        $this->errmsg = & $GLOBALS['errmsg'];
        $this->errinput = & $GLOBALS['errinput'];
        $this->security = & $GLOBALS['security'];



        $this->GetSetup();

      
        /*         * 维护时的提示 */
        if (!isrun) {
            if (false === strpos($_SERVER['SCRIPT_NAME'], admindir)
                    AND false === strpos($this->getip(), '111.160.198.250')
                    AND false === strpos($this->getip(), '101.200.123.1')
                    AND false === strpos($this->getip(), '127.0.0.1')
                    AND false === strpos($this->scriptname, 'notify')//不是支付页,工作人员测试要用到
                    ){
                die($GLOBALS['config']['unisruntext']);
            }
        }



        $this->GetUserInfo();

        /* 如果是店铺then提取店铺信息 */
        if ('bizer' == $this->user['u_gic']) {
            $this->getcominfo($this->user['comid']);
        } else {
            $this->company = false;
        }
    }

    public function __destruct() {
        //if (is_object($this->pdo)) {
        //     unset($this->pdo);
        // }
    }

    function GetSetup() {
        if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
            exit('Request tainting attempted.');
        }
        /* 检测站外提交 */
        //chkpost(); $this->scriptname

        $t = explode('/', $_SERVER['PHP_SELF']);
        $this->scriptname = end($t);
    }

// end func

    function loadclass($classname) {
        if (!is_object($classname)) {
            require_once($classname . '.php');

            $this->page = new ClsPage();
        }
        //require('page.php');
    }

    function ract($s = 'act') {
        if (isset($_POST[$s])) {
            return substr(trim($_POST[$s]), 0, 20);
        } elseif (isset($_GET[$s])) {
            return substr(trim($_GET[$s]), 0, 20);
        } else {
            return null;
        }
    }

    function pact($s = 'act') {
        if (isset($_POST[$s])) {
            $s = substr(trim($_POST[$s]), 0, 20);
        } else {
            $s = null;
        }
        return $s;
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
     * 接收数字
     */
    function rqid($s = 'id', $v = -1) {
        if (isset($_GET[$s])) {
            $x = Trim($_GET[$s]);
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

// end func

    function rfid($s = 'id', $v = -1) {
        if (isset($_POST[$s])) {
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

// end func

    function getRealSize($size) {
        $kb = 1024; // Kilobyte
        $mb = 1024 * $kb; // Megabyte
        $gb = 1024 * $mb; // Gigabyte
        $tb = 1024 * $gb; // Terabyte

        if ($size < $kb) {
            return $size . " B";
        } else if ($size < $mb) {
            return round($size / $kb, 2) . " KB";
        } else if ($size < $gb) {
            return round($size / $mb, 2) . " MB";
        } else if ($size < $tb) {
            return round($size / $gb, 2) . " GB";
        } else {
            return round($size / $tb, 2) . " TB";
        }
    }

    /* 移除数据字段标签 */

    function removemdbfield($s, $sheetname) {
        $myfield = $this->getfieldnamelist($sheetname);

        foreach ($myfield as $key => $value) {
            if (strpos($s, '{$' . $value) != FALSE) {
                $s = preg_replace('/{\$' . $value . '[^}]*}/', '', $s);
            }
        }
        return $s;
    }

    /* 提取数据表字段数组 */

    function getfieldnamelist($sheetname) {
        //这里有问题
        //$this->cache->delete(CacheName . 'table_' . $sheetname);
        $myfield = $this->cache->get(CacheName . 'table_' . $sheetname);

        if (FALSE === $myfield) {
            $myfield = $this->pdo->getFields($sheetname);
            $this->cache->save(CacheName . 'table_' . $sheetname, $myfield);
        }
        return $myfield;
    }

    /**
      接收并检测input
      检测是否数字时必须设为必填项,否则检测是也当必填项处理

     * 1. name			: input name     
     * 2. label		: 
     * 3. mix			: 最小字符,=0时不作限制
     * 4. max			: 最大字符,=0是不作限制
     * 5. inputtype     : 类型(char = 字符, num=数字, int=int, phone, mail, mobile, date, identity=身份证, folder, numf, loginpass, price  )
     * 6. myfilter		: invalid, encode, maxpwd
     * 7. mustfill     : 是否必填, mustfill不等于true,false时，就是默认值
     * _posttype        : get还是post,不对cp判断了，如果是数组，then自动合并成字符串
     */
    function request($name, $label, $mix = 0, $max = 0, $inputtype, $myfilter = '', $mustfill = TRUE) {
        if (null == $this->security)
            LoadClass('security', 'CI_Security', 'Security.php');

        $isarray = false; //如果提交上来的是数组 then依次检测每一项

        /* 接收数据 */
        switch ($this->posttype) {
            case 'get':
                if (isset($_GET[$name])) {

                    $s = trim($_GET[$name]);
                } else {

                    $s = '';
                }

                break;
            case 'post':
                if (isset($_POST[$name])) {
                    $s = trim($_POST[$name]);
                } else {
                    $s = '';
                }
                break;
        }

        /* 检测是否必填 */
        if ('' == $s) {
            /* 必填时 */
            if ($mustfill) {
                //stop(sss);
                $this->errmsg[] = '请填写 ' . $label;
                $this->errinput[] = $name;
                return '';
            } else {
                return '';
            }
        }

        /* ==== 检测字符类型 */
        switch ($inputtype) {
            case 'num': //数字
                if (!is_numeric($s)) {
                    $this->errmsg[] = $label . '格式错误,应为数字格式';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case 'int': //整数
                $tempbool = FALSE;

                if (is_numeric($s)) {
                    if (is_int($s * 1)) {
                        $tempbool = TRUE;
                    } else {
                        $tempbool = FALSE;
                    }
                } else {
                    $tempbool = FALSE;
                }

                if (FALSE == $tempbool) {
                    $this->errmsg[] = $label . '必须是整数';
                    $this->errinput[] = $name;
                    return;
                }

                break;
            case 'price':
                if (!is_numeric($s)) {
                    $this->errmsg[] = $label . '格式错误,应为数字格式';
                    $this->errinput[] = $name;
                    return;
                }

                /* 判断小数点位数不能大于两位 */
                if (!$this->is2price($s)) {
                    $this->errmsg[] = $label . '小数后不能大于两位';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case 'char':
                break;
            case 'date':
                if (!strtotime($s)) {
                    $this->errmsg[] = $label . '格式错误';
                    $this->errinput[] = $name;
                    return;
                }

                break;
            case 'phone':
                if (!is_phone($s)) {
                    $this->errmsg[] = $label . '应为电话格式';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case 'mobile':
                if (!is_mobile($s)) {
                    $this->errmsg[] = $label . '格式错误';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case 'mail':
                if (!is_email($s)) {
                    $this->errmsg[] = $label . '格式错误';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case 'zipcode':
                if (!is_zipcode($s)) {
                    $this->errmsg[] = $label . '格式错误';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case 'identity':
                if (!isIdentity($s)) {
                    $this->errmsg[] = $label . '格式错误';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case 'loginpass':
                if (loginpass($s)) {
                    $this->errmsg[] = $label . '只可以为数字、字母或组合';
                    $this->errinput[] = $name;
                    return;
                }
                break;
            case '':
                break;
            default:
                $this->errmsg[] = $label . '的类型限制错误!';
                $this->errinput[] = $name;
                return;
                break;
        }

        /* 检测字符范围 */
        /* 检测字符必须大于多少,1.如果必填,不满足最小字符时提示错误, 必填的情况已经被''==$s排除了 */
        if ('num' == $inputtype Or 'int' == $inputtype) {
            if ($s * 1 < $mix) {
                $this->errmsg[] = $label . '必须大于或等于' . $mix;
                $this->errinput[] = $name;
                return;
            }
            if ($s * 1 > $max) {
                $this->errmsg[] = $label . '不能大于' . $max;
                $this->errinput[] = $name;
                return;
            }
        }

        /* 除了数字就是字符了 */ else {
            if ($mix > 0 && $mix != $max) {
                if ($this->strlen($s) < $mix) {
                    $this->errmsg[] = $label . '的字符数为' . $mix . '位至' . $max . '位';
                    $this->errinput[] = $name;
                    return;
                }
            }
            /* 检测字符必须小于多少 */
            if ($max > 0 && $mix != $max) {
                if ($this->strlen($s) > $max) {
                    $this->errmsg[] = $label . '的字符数为' . $mix . '位至' . $max . '位';
                    $this->errinput[] = $name;
                    return;
                }
            }
            /* 检测字符数必须为多少 */
            if ($mix === $max) {
                if ($this->strlen($s) != $max && $this->strlen($s) != $mix) {
                    $this->errmsg[] = $label . '的位数必须等于' . $max;
                    $this->errinput[] = $name;
                    return;
                }
            }
        }


        /* ==== 过滤 */
        /* 提交的是文件夹
         * 文件夹没进行判断，直接过滤了
         *  */
        if (false !== strpos($myfilter, 'folder')) {

            $s = $this->security->sanitize_filename($s);
            $myfilter = str_replace('folder', '', $myfilter);
        }

        /* 过滤非法字符, 只允许中,英文,数字 */
        if (strpos($myfilter, 'invalid') !== false) {
            if (invalidreg($s)) {
                $this->errmsg[] = $label . '含有非法字符';
                $this->errinput[] = $name;
            }
            $myfilter = str_replace('invalid', '', $myfilter);
        }

        if (strpos($myfilter, 'ench') !== false) {
            if (truename($s)) {
                $this->errmsg[] = $label . '含有非法字符';
                $this->errinput[] = $name;
            }
            $myfilter = str_replace('ench', '', $myfilter);
        }

        /* 过滤非法字符, 只允许英文,数字 */
        if (strpos($myfilter, 'passstyle') !== false) {
            if (loginpass($s)) {
                $this->errmsg[] = $label . '只可以为数字、字母或组合';
                $this->errinput[] = $name;
            }
            $myfilter = str_replace('passstyle', '', $myfilter);
        }

        if (strpos($myfilter, 'maxpwd') !== false) {
            if (maxpwd($s)) {
                $this->errmsg[] = $label . '必须为数字和字母的组合';
                $this->errinput[] = $name;
            }
            $myfilter = str_replace('maxpwd', '', $myfilter);
        }

        if (strpos($myfilter, 'encode') !== false) {
            $s = htmlencode($s);
            $myfilter = str_replace('encode', '', $myfilter);
        }
//
//
//        if (strpos($myfilter, 'numf') !== false) {
//            $t = (strlen(strstr($s, '.')) - 1);
//            if ($t > 2) {
//                $this->errmsg[] = $label . '格式错误（请保留两位小数）';
//                $this->errinput .= ($name . ',');
//            }
//            $myfilter = str_replace('numf', '', $myfilter);
//        }
//
//        if (strpos($myfilter, 'lonlat') !== false) {
//            $t = (strlen(strstr($s, '.')) - 1);
//            if ($t > 5) {
//                $this->errmsg[] = $label . '格式错误（请保留五位小数）';
//                $this->errinput .= ($name . ',');
//            }
//            $myfilter = str_replace('lonlat', '', $myfilter);
//        }    
        return $s;
    }

    function sendmail($mail) {
        $c_mail = new Cls_Mail();
        $type = 'bindmail';
        $str = $GLOBALS['cache']->get('user_' . $GLOBALS['we']->user['id']);
        if ($str) {
            $array = json_decode($str, true);
            if ($array['stime']) {
                $timediff = time() - $array['stime'];
                // if($timediff<60){
                // 	ajaxerr('请稍后再点击获取邮件');
                // }
                $randcode = $array['randcode'];
            }
        } else {
            $randcode = $GLOBALS['we']->generate_randchar(8);
            $array['randcode'] = $randcode;
            $array['stime'] = time();
            $GLOBALS['cache']->save('user_' . $GLOBALS['we']->user['id'], json_encode($array), 86400);
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/service/validpass.php?act=chkmail&serve=' . $GLOBALS['we']->user['id'] . '&type=' . $type . '&randcode=' . $randcode . '&mail=' . $mail;
        $a = '<a href="' . $url . '">' . $url . '</a>';

        $subject = '邮箱验证';
        $content = '<p>您好：</p>

            <p>欢迎您申请邮箱验证，请您点击以下链接完成邮箱的绑定验证，此链接有效期为24小时<br>

            ' . $a . '<br>如点击无法打开，您可以尝试将以上链接复制粘贴到浏览器地址栏中打开。</p>';
        $mail_res = $c_mail->smtp_mail($mail, $subject, $content);

        if (!$mail_res) {
            ajaxerr('发送失败，请验证输入邮箱是否输入正确');
        }
    }

    /**
     * 接收idlist. 接收传过来的数组变字符串
     */
    function ridlist($name, $method = 'post') {
        if ('post' == $method And isset($_POST[$name])) {
            $s = $_POST[$name];
        } else if ('get' == $method And isset($_GET[$name])) {
            $s = $_GET[$name];
        } else {
            return '';
        }



        if (is_array($s)) {
            for ($i = 0; $i < count($s); $i++) {
                if (!is_numeric($s[$i])) {
                    return '';
                }
            }
            $s = join(',', $s);
        } else {
            $s = '';
        }
        return $s;
    }

    /* 接收传过来的字符串列表
     * 返回字符串
     */

    function rqidlist($name, $method = 'get') {
        $s = '';
        $b = array();


        if ('get' == $method) {
            if (isset($_GET[$name])) {
                $s = $_GET[$name];
            }
        } else {
            if (isset($_POST[$name])) {
                $s = $_POST[$name];
            }
        }

        if ('' != $s) {
            $a = explode(',', $s);


            for ($i = 0; $i < count($a); $i++) {
                if (is_numeric($a[$i])) {
                    $b[] = $a[$i];
                }
            }
            return implode(',', $b);
        } else {
            return '';
        }
    }

    function getstring($name = null) {
        if (null === $name) {
            return '';
        } else {
            if (isset($name)) {
                return $_GET[$name];
            } else {
                return '';
            }
        }
    }

    function ric($name, $method = 'get') {
        $s = '';

        if ('get' == $method) {
            if (isset($_GET[$name])) {
                $s = $_GET[$name];
            }
        } else {
            if (isset($_POST[$name])) {
                $s = $_POST[$name];
            }
        }

        if (invalidreg($s)) {
            showerr($name . '含有非法字符:' . $s);
        } else {
            return $s;
        }
    }

    /* 执行sql,返回数组,自动处理翻页 */

    function exers($sql, $para = null, $maxpage = 0) {
        $this->getcurrentpage();

        if (0 == $maxpage) {
            $maxpage = MaxPage;
        }
        $this->mymaxpage = $maxpage;

        if (1 === $this->currentpage) {
            $startitem = 0;
        } else {
            $startitem = ($this->currentpage - 1) * $maxpage;
        }

        $sql .= ' limit ' . $startitem . ',' . $maxpage;

        return $this->pdo->fetchAll($sql, $para, true);
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

    function geturlfrompara($filename = null) {
        $a = array();
        $url = '';

        /* 取文件名 */
        if (null === $filename) {
            $filename = $this->scriptname;
        }

        //这里没对$_GET[]进行检测
        //$_GET['page'] = '{$page}';
        $tget = $_GET;

        /* 如果有page就移除了 */
        if (array_key_exists('page', $tget)) {
            unset($tget['page']);
        }

        foreach ($tget as $k => $v) {
            $a[] = $k . '=' . $v . PHP_EOL;
        }

        if (count($a) > 0) {
            $para = join('&amp;', $a);
            $url = $filename . '?' . $para;
        } else {
            $url = $filename;
        }



        return $url;
    }

// end func

    function mappath($path) {
        return str_replace('/', '\\', $_SERVER['DOCUMENT_ROOT'] . $path);
    }

// end func

    function repnode($mdbname, $node, $m_tr) {
        $myfield = $this->getmdbfield($mdbname, $m_tr);
        $a = split($myfield, ',');

        $mycount = count($a);

//	$s = $m_tr;
//
//	for ($i=0; $i<($mycount+1); $i++) { /*最后一个是空值,所以不计算*/
//		$s = str_replace($s, '{$' .$a[$i]. '}', );
//	}
//	For i=0 To jinfangfield '<syl>最后一个是空值,所以不计算<by syl>
//			s = Replace(s,"{$"& a(i) &"}", node.getAttribute(a(i))&"")
//	Next
//
//	return $s;
    }

// end func

    function read_file($file) {
        if (!file_exists($file)) {
            return FALSE;
        }

        if (function_exists('file_get_contents')) {
            return file_get_contents($file);
        }

        if (!$fp = @fopen($file, FOPEN_READ)) {
            return FALSE;
        }

        flock($fp, LOCK_SH);

        $data = '';
        if (filesize($file) > 0) {
            $data = fread($fp, filesize($file));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $data;
    }

    function write_file($path, $data, $mode = FOPEN_WRITE_CREATE_DESTRUCTIVE) {
        if (!$fp = @fopen($path, $mode)) {
            return FALSE;
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);

        return TRUE;
    }

    /* 从SQL语句截取表名 */

    function gettablename($str) {
        $str = chop($str);
        $a = explode(' from ', $str);
        $str = $a[1];

        $a1 = explode(' ', $str);
        $str = $a1[0];
        $str = str_replace('`', '', $str);

        return $str;
    }

// end func


    /* 从html里提取有什么字段 */

    function getmdbfield($tablename, $str, $strback = null) {
        //取出这个表的字段列表
        $a = $this->getfieldnamelist($tablename);
        $mycount = count($a);

        $str .= '{$id}{$uid}{$classid}'; //何时都加上字段id

        $myfield = '';

        for ($i = 0; $i < $mycount; $i++) {
            if (stripos($str, '{$' . $a[$i]) !== false) {
                $myfield .= $a[$i] . ',';
            }
        }

        /* 看看返回字段里有没有 */
        if ($strback != null) {
            $a = explode(',', $strback);
            $mycount = count($a);

            for ($i = 0; $i < $mycount; $i++) {
                if (stripos($myfield, $a[$i]) === false) {
                    $myfield .= $a[$i] . ',';
                }
            }
        }
        return substr($myfield, 0, strlen($myfield) - 1);
    }

// end func


    /* 判断是否包含某字符 */

//$s1:大字符
//$s2:小字符
    function ins($s1 = '', $s2 = '') {
        if ('' == $s1 || '' == $s2) {
            return false;
        }

        $s2 = ',' . $s2 . ',';
        $s1 = ',' . $s1 . ',';

        if (strpos($s1, $s2) !== false) {
            return true;
        } else {
            return false;
        }
    }

// end func
//删除目录及目录下的文件
//循环删除目录和文件函数  
    function delDirAndFile($dirName) {
        if ($handle = opendir("$dirName")) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        delDirAndFile("$dirName/$item");
                    } else {
                        if (unlink("$dirName/$item"))
                            echo "成功删除文件： $dirName/$item<br />\n";
                    }
                }
            }
            closedir($handle);
            if (rmdir($dirName))
                echo "成功删除目录： $dirName<br />\n";
        }
    }

    /**
      检测验证码是否正确,
     * 返回true,
     */
    function codeistrue() {
        $codestr = $GLOBALS['we']->request('验证码', 'codestr', 'post', 'char', 0, 50, 0);

        if ($codestr == '' OR ! isset($_SESSION['codestr'])) {
            return 'isN';
        }

        if ($_SESSION['codestr'] !== $codestr) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证图片验证码
     */
    function validcode($code) {
        $code = strtolower($code);
        $_SESSION['ejiayuding_valid'] = strtolower($_SESSION['ejiayuding_valid']);
        if ($code != '' && $code == $_SESSION['ejiayuding_valid']) {
            unset($_SESSION['ejiayuding_valid']);
            return TRUE;
        }
        return FALSE;
    }

    /*
     * 释放验证码
     */

    function releasecode() {
        unset($_SESSION['codestr']);
        session_destroy();
    }

// end func

    /**
     * 检测信息来源.
     * @param   type    $varname    description
     * @return  type    description
     * @access  public or private
     * @static  makes the class property accessible without needing an instantiation of the class
     */
    function chkpost() {
        $method = $_SERVER['PHP_SELF'];
        if ($_POST) {
            $a = $_SERVER['HTTP_REFERER'];
            $b = "http://{$_SERVER['SCRIPT_NAME']}" . $method;
            if (strcmp($b, $a) == 0) {
                //echo '允许';
                return TRUE;
            } else {
                //die('不许站外提交');
                return FALSE;
            }
        }
    }

// end func

    /**
     * Short description.
     * @param   type    $varname    description
     * @return  type    description
     * @access  public 
     * @static  makes the class property accessible without needing an instantiation of the class
     */
    function getarrvalue($myname, $myid, $mytitle, $mytype = 'byid') {
        $arr = $this->getarr($myname);

        if (FALSE === $arr) {
            return FALSE;
        }

        switch ($mytype) {
            case 'byid':
                if (isset($arr[$myid][$mytitle]))
                    return $arr[$myid][$mytitle];
                break;
        }
    }

// end func

    /**
     * 从数据库生成数组缓存.
     * @return  type    TRUE OR FALSE
     */
    function getarr($myname, $myid = null, $myic = null) {
        switch ($myname) {
            case 'group' :
                $t = $this->cache->get(CacheName . $myname);

                if (FALSE == $t) {

                    $sql = 'select * from `' . sh . '_group` order by cls asc,id asc';

                    $a = $this->pdo->fetchAll($sql);

                    $t = $this->rstoarr($a, 'ic');

                    $this->cache->save(CacheName . $myname, $t);
                }
                break;

            case 'channel' :
                $t = $this->cache->get(CacheName . $myname);

                if (FALSE == $t) {

                    $sql = 'select * from `' . sh . '_channel` where isuse=1 order by cls asc, id asc';

                    $a = $this->pdo->execute($sql);
                    $t = $this->rstoarr($a['rs']);

                    $this->cache->save(CacheName . $myname, $t);
                }

                break;

            case 'special' :
                $t = $this->cache->get(CacheName . $myname);

                if (FALSE == $t) {
                    $sql = 'select * from `' . sh . '_special` order by cls asc, id asc';

                    $a = $this->pdo->execute($sql);
                    $t = $this->rstoarr($a['rs']);

                    $this->cache->save(CacheName . $myname, $t);
                }
                break;
        }

        if ($myid != null) {
            $myid .='';
            if (array_key_exists($myid, $t)) {
                return $t[$myid];
            } else {
                return FALSE;
            }
        }

        $a = null;

        if ($myic != null) {
            foreach ($t as $v) {
                if ($v['ic'] == $myic) {
                    $a = $v;
                }
            }

            if (null !== $a) {
                return $a;
            } else {
                return false;
            }
        } else {
            return $t;
        }
    }

// end func

    function ictoid($myid, $mysheetname) {
        $t = $this->cache->get(CacheName . $mysheetname . 'id');

        if (false == $t) {
            $sql = 'select id,ic from ' . sh . $mysheetname;

            $rs = $this->execute($sql);

            $t = array();

            foreach ($rs['rs'] as $v) {
                $t[(string) ($v['ic'])] = $v['id'];
            }

            $this->cache->save(CacheName . $mysheetname . 'id', $t);
        }

        if (array_key_exists($myid, $t)) {
            return $t[$myid];
        } else {
            return false;
        }
    }

// end func

    function getidfromic($myic, $mysheetname) {

        $rs = $this->getarr($mysheetname);

        foreach ($rs as $v) {
            if ($myic == $v['ic']) {
                return $v['id'];
            }
        }

        return false;
    }

// end func

    function loadchannel() {

        $sql = 'select * from `' . sheet . '_channel` where isuse=1 order by cls asc, id asc';
    }

// end func

    function readchannel($x, $myfield = 'ic') {
        $sql = 'select * from `' . sheet . '_channel` where 1=1';
        $sql .= ' and isuse=1 ';

        switch ($myfield) {
            case 'ic' :
                $sql .= ' and ic="' . $x . '"';
                break;
            case '':
                $sql .= ' and id=' . $x;
                break;
        }

        $sql .= ' order by cls asc, id asc';


        $result = $this->exeone($sql);

        return $result;
    }

    /* 清除缓存 */

    function deletecache($myname) {
        $this->cache->delete(CacheName . $myname);
        $this->cache->delete(CacheName . $myname . 'id');
    }

    /**
     * 取得某个模块的分类缓存.
     */
    function getclass($cid = null, $module = null, $myid = null, $myic = null) {
        /* 没有cid和模块是返回false */
        if ($cid == null AND $module == null) {
            return FALSE;
        }


        $myname = CacheName . 'class_' . ($cid . '_' . $module);

        $t = $this->cache->get($myname);

        if (FALSE == $t) {
            $sql = 'select * from `' . sheet . '_class` where 1=1 ';
            if ($cid != null) {
                $sql .= ' and cid=' . $cid;
            } else {
                $sql .= ' and module="' . $module . '"';
            }
            $sql .= ' order by treeid asc ';
            $rs = $this->pdo->execute($sql);

            $t = $this->rstoarr($rs['rs']);

            $this->cache->delete($myname);
            $this->cache->save($myname, $t);
        }


        if ($myid != null) {
            $myid .='';
            if (array_key_exists($myid, $t)) {
                return $t[$myid];
            } else {
                return FALSE;
            }
        }

        //跟据ic返回
        $a = null;

        if ($myic != null) {
            foreach ($t as $v) {
                if ($v['ic'] == $myic) {
                    $a = $v;
                }
            }

            if (null !== $a) {
                return $a;
            } else {
                return false;
            }
        } else {
            return $t;
        }

        return $t;
    }

// end func

    function delclass() {
        
    }

    /**
     * 由rs变为数组.
     * keyfield: 用哪个字段做为键值
     */
    function rstoarr($rs, $keyfield = 'id') {
        $mycount = count($rs);

        if (0 == $mycount) {
            return $rs;
        }

        $a = array();


        for ($i = 0; $i < $mycount; $i++) {
            foreach ($rs[$i] as $k => $v) {
                $a[(string) ($rs[$i][$keyfield])][$k] = $v;
            }
        }

        return $a;
    }

// end func

    /* 向url追加参数 */

    function addpara($str, $para) {
        /* 没问号 */
        if (FALSE === stripos($str, '?')) {
            return $str . '?' . $para;
        } else {
            return $str . '&amp;' . $para;
        }
    }

    function loginout() {
        scookie('user', '', time() - 3600);
        unset($_SESSION[CacheName . 'user']);
        session_destroy();
    }

    /**
     * 得到用户基本资料.
     */
    function GetUserInfo() {
        /* if 没有session then 生成一个session */
        if (!isset($_SESSION [CacheName . 'user'])) {
            // echo 'a';

            /* 没有cookie时直接写入游客信息 */
            if (!isset($_COOKIE [CacheName . 'user'])) {
                $this->user = $this->GetGuestInfo(); // session 写入游客信息
            } else {

                /* 有cookie时, 从cookie提取用户ID和密码, => 检测是否正确 => 不正确写入游客信息 */
                $user = unserialize($_COOKIE [CacheName . 'user']);

                $user ['id'] = $this->strcode($user ['id'], 'DECODE');

                /* 检测从cookie提取的用户名和密码是否正确, 不正确then写入游客信息 */
                //if (!$this->chkuserlogin('', $user ['pass'], $user ['savecookie'], $user ['id'], 'cookie')) {
                //    $this->GetGuestInfo();
                //}
            }
        } else {
            $this->user = $_SESSION [CacheName . 'user'];
        }
    }

    function getcominfo($comid) {
        $sql = 'select * from `' . sh . '_com` where 1 ';


        $sql .= ' and id=:comid';


        $result = $this->pdo->fetchOne($sql, Array(':comid' => $comid));

        $this->company = $result;
    }

    /*
     * 写入游客信息
     */

    function GetGuestInfo() {
        $user['id'] = 0;
        $user['u_name'] = 'Guest';
        $user['u_nick'] = '临时游客';
        $user['u_fullname'] = '游客';

        $user['u_mobile'] = '无';

        $user['u_gic'] = 'guest';
        $user['u_gname'] = '游客';

        $user['u_roleic'] = ''; //角色编码
        $user['u_rolename'] = ''; //角色编码

        $user['u_face'] = '/_images/noface.png';

        //$user['u_ismaster'] = 0;

        $user['comid'] = 0;
        $user['comname'] = '';

        return $user;
    }

//    function chkuser() {
//        //不是管理员转入登录页
//        if (!$this->ismember) {
//            $href = '/service/login.php';
//            autolocate($href, 0);
//        }
//    }

    /**
     * 当前时间.
     * @return  type    datetime
     */
    function now() {
        return date('Y-m-d H:i:s');
    }

// end func
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                       |
// +----------------------------------------------------------------------+
// | Copyright (c)   2003 The individual                                    |
// +----------------------------------------------------------------------+
// |It agrees without being passing through the very person and it is      |
// |conceited secretly using the after result.                             |
// +----------------------------------------------------------------------+
// | Authors: Original Author   Allan Kent                                  |
// |           Editing           Dandelion                                   |
// +----------------------------------------------------------------------+

    /**
     * @Purpose:
     * It returns to the time interval during two dates.
     * @Method Name: DateDiff().
     * @Parameter: string $interval -->The time interval character string numerical    formula.
     *                                   w -->Weekday
     *                                   d -->Day
     *                                   h -->Hour
     *                                   n -->Minute
     *                                   s -->Second
     *            string $date1     -->It represents as time() a form in the time of the first date.
     *            string $date2     -->It represents as time() a form in the time of the second date.
     * @Return: string $retval    -->Return a new as time() a form in the time of the date.
     * @See: string bcdiv(string left operand,string right operand, int [scale]).
     * $date1,date2 -->数字格式
     */
    function datediff($interval, $date1, $date2) {
        // @See: It gets the number of the seconds in the one of the 2nd period day interval.
        $time_difference = $date2 - $date1;
        switch ($interval) {

            case "w": $retval = bcdiv($time_difference, 604800);
                break;
            case "d": $retval = bcdiv($time_difference, 86400);
                $date2_fm = date('Y-m-d H:i:s', $date2);
                $date1_fm = date('Y-m-d H:i:s', $date1);

                $date2 = strtotime($date2_fm);
                $date1 = strtotime($date1_fm);

                $retval = bcdiv(($date2 - $date1), 86400, 0);

                break;
            case "D":
                $date2_fm = date('Y-m-d', $date2);
                $date1_fm = date('Y-m-d', $date1);

                $date2 = strtotime($date2_fm);
                $date1 = strtotime($date1_fm);
                $retval = bcdiv(($date1 - $date2), 86400, 0);
                break;
            case "h": $retval = bcdiv($time_difference, 3600);
                break;
            case "n": $retval = bcdiv($time_difference, 60);
                break;
            case "s": $retval = $time_difference;
                break;
        }
        return $retval;
    }

    function DateAdd($part, $number, $date, $format = 'Y-m-d H:i:s') {
        $date_array = getdate(strtotime($date));

        $hor = $date_array["hours"];
        $min = $date_array["minutes"];
        $sec = $date_array["seconds"];
        $mon = $date_array["mon"];
        $day = $date_array["mday"];
        $yar = $date_array["year"];

        switch ($part) {
            case "year": $yar += $number;
                break;
            case "q": $mon += ($number * 3);
                break;
            case "mon": $mon += $number;
                break;
            case "week": $day += ($number * 7);
                break;
            case "day": $day += $number;
                break;
            case "hour": $hor += $number;
                break;
            case "minute": $min += $number;
                break;
            case "setond": $sec += $number;
                break;
        }
        return date($format, mktime($hor, $min, $sec, $mon, $day, $yar));
    }

//变成unix时间
    function dateunix($interval, $date) {
        $v = '';

        $t = strtotime($date);

        switch ($interval) {
            case 'w' : $v = bcdiv($t, '604800');
            case 'd' : $v = bcdiv($t, '86400');
            case 'h' : $v = bcdiv($t, '3600');
            case 'n' : $v = bcdiv($t, '60');
            case 's' : $v = $t;
        }

        return $t;
    }

// end func


    /* 获取ip地址 */

    function getip() {
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

    /**
     * Validate the syntax of the given IP adress
     *
     * This function splits the IP address in 4 pieces
     * (separated by ".") and checks for each piece
     * if it's an integer value between 0 and 255.
     * If all 4 parameters pass this test, the function
     * returns true.
     *
     * @param  string $ip IP adress
     * @return bool       true if syntax is valid, otherwise false
     */
    function check_ip($ip) {
        $oct = explode('.', $ip);
        if (count($oct) != 4) {
            return false;
        }

        for ($i = 0; $i < 4; $i++) {
            if (!preg_match("/^[0-9]+$/", $oct[$i])) {
                return false;
            }

            if ($oct[$i] < 0 || $oct[$i] > 255) {
                return false;
            }
        }

        return true;
    }

    /**
     * 加密、解密字符串
     *
     * @global string $db_hash
     * @global array $pwServer
     * @param $string 待处理字符串
     * @param $action 操作，ENCODE|DECODE
     * @return string
     */
    function strcode($string, $action = 'ENCODE') {
        $action != 'ENCODE' && $string = base64_decode($string);
        $code = '';
        $key = substr(md5('temp'), 8, 18);
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k = $i % $keyLen;
            $code .= $string[$i] ^ $key[$k];
        }
        return ($action != 'DECODE' ? base64_encode($code) : $code);
    }

    /* 原目录，复制到的目录 */

    function recurse_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /* 生成随机数 */

    function generate_randchar($length = 10, $type = '') {
        // 密码字符集，可任意添加你需要的字符  
        $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789';
        if ($type == 'num')
            $chars = '0123456789';
        if ($type == 'char')
            $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $mychar = '';
        for ($i = 0; $i < $length; $i++) {
            $mychar .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $mychar;
    }

    /* 计算字符数 */

    function strlen($str) {
        $i = 0;
        $count = 0;
        $len = strlen($str);
        while ($i < $len) {
            $chr = ord($str[$i]);
            $count++;
            $i++;
            if ($i >= $len)
                break;
            if ($chr & 0x80) {
                $chr <<= 1;
                while ($chr & 0x80) {
                    $i++;
                    $chr <<= 1;
                }
            }
        }
        return $count;
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

    /* 数组变选项列表 */

    function arroption(&$arr) {

        $li = '';

        $tli = '<option value="{$value}">{$title}</option>' . PHP_EOL;


        foreach ($arr as $k => $v) {
            $s = $tli;
            $s = str_replace('{$title}', $v, $s);
            $s = str_replace('{$value}', $k, $s);
            $li .= $s;
        }

        return $li;
    }

    /* 检测表里是不是有这个标识符
     * 添加时,有重复的就提示
     * 修改时检测id不等于自已的就提示
     * $and = 其它筛选条件
     */

    function hasic($mytable, $myid, $myic, $and = '') {
        $sql = 'select count(*) from ' . $mytable . ' where 1=1 ';
        $sql .= ' and ic="' . $myic . '"';

        if ($myid > 0) {
            $sql .= ' and id<>' . $myid;
        }

        $sql .= $and;

        $result = $this->pdo->counts($sql);

        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    function hasname($tablename, $item, $val, $id = '', $addition = '') {
        $sql = 'select count(*) from `' . $tablename . '` where 1';
        if ('' != $id) {
            $sql .= ' and id<>' . $id;
        }
        $sql.= ' and ' . $item . '="' . $val . '"' . $addition;
        //stop($sql);
        $rs = $this->pdo->counts($sql);

        if ($rs > 0) {
            return true;
        } else {
            return false;
        }
    }

    function sqlparseas($sqlstr) {
        $strfield = '';
        if (strpos($sqlstr, '`')) {
            $sqlstr = explode('`', $sqlstr);
            $sqlstr = substr(trim($sqlstr[0]), 0, -4);
        }
        $str = explode(',', $sqlstr);
        foreach ($str as $v) {
            if (strpos($v, 'as ')) {
                $sqlstr = explode('as ', $v);
                $strfield.=trim($sqlstr[1]) . ',';
            }
        }
        return $strfield;
    }

    //更新session, global->user
    function updatesession($rs) {
        if (!isset($_SESSION[CacheName . 'user'])) {
            return false;
        }

        foreach ($rs as $key => $value) {
            $this->user[$key] = $rs[$key];
            $_SESSION[CacheName . 'user'][$key] = $rs[$key];
        }
    }

    //根据经纬度获取酒店
    function getlocalhotel($local, $limit = 5) {
        if (!isset($local['lat'])) {
            $local['lat'] = 0;
        }
        if (!isset($local['lon'])) {
            $local['lon'] = 0;
        }

        $date1 = strtotime(date("Y-m-d", time()));
        $date2 = strtotime(date("Y-m-d", time() + 86400));
        $sql0 = 'select r.hotelid,r.id';
        $sql0 .= ',min(roomremain) as roomremain,count(r.id) as mycount';
        $sql0 .= ' from `' . sheet . '_room` r  inner join `' . sheet . '_roomlist` rl';
        $sql0 .= ' on r.hotelid=rl.hotelid and r.id=rl.roomid';
        $sql0 .= ' where r.isuse=1';
        $sql0 .= ' and r.isspecial=0';
        $sql0 .= ' and rl.mydate<' . $date2 . ' and rl.mydate>= ' . $date1;
        $sql0 .= ' and rl.roomsupply>0';
        $sql0 .= ' and roomremain>0';
// 	    $sql0 .= ' and r.islock=0';
        $sql0 .= ' and rl.isopen=1';
        $sql0 .= ' group by r.id';
        $sql0 .= ' having( mycount>=(' . $date2 . '-' . $date1 . ')/86400)';
        $sql0 .= ' order by r.islock asc,(case roomremain when 0 then mycls*1000 else mycls end) asc';

        $a = $GLOBALS['we']->execute($sql0);
        $result = $a['rs'];
        foreach ($result as $k => $v) {
            $hotelidarr[] = $v['hotelid'];
        }
        $hotelidarr = implode(',', $hotelidarr);
        $sql = 'select * from `' . sheet . '_hotel` where 1 ';
        $sql .= 'and isopen=1 ';
        $sql .= ' and is_display=0 ';
        if ($local['lon'] != 0) {
            $minlon = 0;
            if (($local['lon'] - 50) > 0) {
                $minlon = $local['lon'] - 50;
            }
            $maxlon = 180;
            if (($local['lon'] + 50) < 180) {
                $maxlon = $local['lon'] + 50;
            }
            $sql .= ' and longitude >=' . $minlon;
            $sql .= ' and longitude <=' . $maxlon;
        }
        if ($local['lat'] != 0) {
            $minlat = 0;
            if (($local['lat'] - 50) > 0) {
                $minlat = $local['lat'] - 50;
            }
            $maxlat = 180;
            if (($local['lat'] + 50) < 180) {
                $maxlat = $local['lat'] + 50;
            }
            $sql .= ' and latitude >=' . $minlat;
            $sql .= ' and latitude <=' . $maxlat;
        }

        if ('' != $hotelidarr) {
            $sql .= ' and id in (' . $hotelidarr . ')';
        } else {
            $sql .= ' and id=0';
        }
        $res = $GLOBALS['we']->execute($sql);
        $hoteldistance = array();
        $hotel = array();
        foreach ($res['rs'] as $key => $value) {
            $hotel[$value['id']] = $value;
            $distance = $this->getDistance($value['latitude'], $value['longitude'], $local['lat'], $local['lon']);
            $hoteldistance[$value['id']] = $distance;
        }
        asort($hoteldistance);
        foreach ($hoteldistance as $key => $value) {
            if ($i > 6)
                continue;
            if ($value > 1000)
                $value = round($value / 1000, 1) . '公里';
            else
                $value = $value . '米';
            $hotel[$key]['distance'] = $value;
            $a_hotel[$i] = $hotel[$key];
            $i++;
        }
        return $a_hotel;
    }

    //计算两点间的距离
    function getDistance($lat_a, $lng_a, $lat_b, $lng_b) {
        //R是地球半径（米）
        $R = 6366000;
        $pk = doubleval(180 / pi());

        $a1 = doubleval($lat_a / $pk);
        $a2 = doubleval($lng_a / $pk);
        $b1 = doubleval($lat_b / $pk);
        $b2 = doubleval($lng_b / $pk);

        $t1 = doubleval(cos($a1) * cos($a2) * cos($b1) * cos($b2));
        $t2 = doubleval(cos($a1) * sin($a2) * cos($b1) * sin($b2));
        $t3 = doubleval(sin($a1) * sin($b1));
        $tt = doubleval(acos($t1 + $t2 + $t3));

        return round($R * $tt);
    }

    function getchildclass($classid) {
        if ($classid == '' || $classid == false) {
            return false;
        }
        $sql = 'select id from `' . sheet . '_class` where pid=' . $classid . ' and isshow=1';
        $res = $this->executers($sql);
        return $res;
    }

    function isadmin($uid) {
        $sql = 'select * from ' . sheet . '_admin where u_id=' . $uid . ' limit 1';
        $rs = $this->exeone($sql);
        if ($rs != false) {
            return TRUE;
        }
        return false;
    }

    //return数组或echo json
//    function output(&$s) {
//        if (isset($_POST['outtype'])) {
//            echo json_encode($s);
//        } else {
//            return $s;
//        }
//    }

    function runtime() {
        return (round((microtime(true) - pagestarttime), 4));
    }

    /* 添加日志
     * $rs的键值
     * mytype : goodshit(商品浏览) order(下定单) pay (
     * 
     * 除mytype外，还需要传过来
     * doorid
     * comid
     * placeid
     * deviceid
     * goodsid
     * comgoodsid
     * price
     * 
     */

    function dolog($rs) {
        $rs['stimeint'] = time();
        $rs['myip'] = $this->getip();
        $rs['uid'] = $this->user['id'];

        $this->pdo->insert(sh . '_counts', $rs);

        return true;
    }

    function is2price($price) {

        $a = explode('.', $price);


        if (count($a) < 2) {
            return true;
        }

        if (strlen($a[1]) > 2) {
            return false;
        } else {
            return true;
        }
    }

    /* 提收，检测
     * 需要先提供本类的posttype
     * 传入默认开始时间，默认结止时间,整型
     * 返回数组，包含 date1,date2,int1,int2 如果出错，则返回全局错误信息
     *      */

    function getdates($datebegin_int, $dateend_int) {
        $a = array();

        $date1 = $this->request('date1', '开始时间', 1, 50, 'date', '', false);
        $date2 = $this->request('date2', '结止时间', 1, 50, 'date', '', false);

        /* 即使出错了，也返回原来的值 */
        $a['date1'] = $date1;
        $a['date2'] = $date2;



        /* check date */
        if ('' == $date1) {
            $date1_int = $datebegin_int;
        } else {
            $date1_int = strtotime($date1);
        }

        if ('' == $date2) {
            $date2_int = $dateend_int;
        } else {
            $date2_int = strtotime($date2);
        }

        if ($date1_int > $date2_int) {
            $this->errmsg[] = '开始时间不能大于结止时间';
            return $a;
        }

        $a['date1'] = date('Y-m-d', $date1_int);
        $a['date2'] = date('Y-m-d', $date2_int);
        $a['int1'] = $date1_int;
        $a['int2'] = $date2_int;

        return $a;
    }

    /* 检测是否是int */

    function isint($s) {
        $tempbool = FALSE;

        if (is_numeric($s)) {
            if (is_int($s * 1)) {
                $tempbool = TRUE;
            } else {
                $tempbool = FALSE;
            }
        } else {
            $tempbool = FALSE;
        }

        if (FALSE == $tempbool) {
            return false;
        } else {
            return true;
        }
    }

    /* 存cookie
     * 方便有统一的cookie入口做统计
     */

    //function scookie($name, $value, $time) {
    //    setcookie(CacheName . $name, $value, $time, '/');
    // }
// end f
}

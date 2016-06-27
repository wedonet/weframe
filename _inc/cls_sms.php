<?php

require(syspath . '/_sms/yunpiansms.php'); //北京华兴短信接口

class cls_sms {

    function __construct() {
        
    }

    function sendsms($mobile, $msg) {
        $target = "http://cf.lmobile.cn/submitdata/Service.asmx/g_Submit";
        // 替换成自己的测试账号,参数顺序和wenservice对应
        // $post_data = 'sname=dlsdcs00&spwd=g0k0lGoS&scorpid=&sprdid=1012818&sdst=13043272481&smsg='.rawurlencode('尊敬的用户：http://weibo.com测试123456是您本次的短信验证码，5分钟内有效.【百分信息】');
        $post_data = "sname=dlssbw04&spwd=bw123456&scorpid=&sprdid=1012818&sdst=" . $mobile . "&smsg=" . rawurlencode($msg);
        $gets = Post($post_data, $target);
        $gets = parsesendinfo($gets);
        return $gets;
    }

    function parsesendinfo($str) {
        $strarr = array();
        $res = array();
        if (strpos($str, '<State>') && strpos($str, '</State>')) {
            $strarr = explode("<State>", $str);
            $str = $strarr[1];
            $strarr = explode("</State>", $str);
            $res['sendstate'] = $strarr[0];
            $str = $strarr[1];
            if (strpos($str, '<MsgState>') && strpos($str, '</MsgState>')) {
                $strarr = explode("<MsgState>", $str);
                $str = $strarr[1];
                $strarr = explode("</MsgState>", $str);
                $res['sendmsgstate'] = $strarr[0];
            }
            return $res;
        } else {
            return false;
        }
    }

    function insertsendLog($rs) {
        $insertid = $GLOBALS ['pdo']->insert(sh . '_sendsms', $rs);
        //return true;
    }

    function usersms_($res, $order) {
        $rs = getsendmsg($res, $order);

        if ($rs == false)
            return false;
        $rs['sendmobile'] = $order['guestmobile'];
        //$memmobilearr=array(3,4,5,6,14,15,19,23,28,31,32);
        $memmobilearr = array(4, 5, 6, 15, 19, 23, 28, 31, 32);
        if (in_array($rs['sendtype'], $memmobilearr)) {
            $order['guestmobile'] = getsendmemmobile($order);
            if ($order['guestmobile'] != false)
                $rs['sendmobile'] = $order['guestmobile'];
            //stop($order['guestmobile']);
        }
        //stop($order['guestmobile']);
        //if(issendsms==1)$res=sendsms($order['guestmobile'], $rs['sendmsg']);//百威短信发送接口	
        /* if(issendsms==1){//北京华兴短信接口
          $httpsend=new HttpSend();
          $res=$httpsend->smshttpSend($order['guestmobile'], $rs['sendmsg']);
          if(!$res)return false;
          } */

        if (issendsms == 1) {//北京云片网短信接口
            $httpsend = new yunpiansms();
            $res = $httpsend->getsendres($order['guestmobile'], $rs['sendmsg']);
            if (!$res)
                return false;
        }

        if (isset($res['sendstate']) && isset($res['sendmsgstate'])) {
            $rs['sendstate'] = $res['sendstate'];
            $rs['sendmsgstate'] = $res['sendmsgstate'];
        }
        //if(issendsms==1)sendsms($order['guestmobile'], $rs['sendmsg']);
        $rs['senduid'] = $order['uid'];
        if (isset($order['hotelid']))
            $rs['sendcomid'] = $order['hotelid'];
        if (isset($GLOBALS ['we']->user ['id']))
            $rs['sendduid'] = $GLOBALS ['we']->user ['id'];
        $rs['sendtimeint'] = time();
        $rs['sendip'] = $GLOBALS['we']->getip();
        //$dir =  sysdir.'_sms/log/'.time().'.txt';
        //$GLOBALS['we']->write_file($dir , implode(',',$rs));
        if (issendsms == 1)
            insertsendlog($rs);
        return true;
    }

    /* 发送短信
     * mobile : 手机号
     * msg : 短信内容
     * $para : 数组 
     *      $para['uid']
     *      $para['comid']
     *      */

    function send($mobile, $msg, $para) {

        //stop($order['guestmobile']);
        //if(issendsms==1)$res=sendsms($order['guestmobile'], $rs['sendmsg']);//百威短信发送接口	
        /* if(issendsms==1){//北京华兴短信接口
          $httpsend=new HttpSend();
          $res=$httpsend->smshttpSend($order['guestmobile'], $rs['sendmsg']);
          if(!$res)return false;
          } */

        if ($GLOBALS['config']['issendsms'] == 1) {//北京云片网短信接口
            $httpsend = new yunpiansms();
            $res = $httpsend->getsendres($mobile, $msg);
           
            if (!$res) {
                return false;
            }
        } else {
            return false;
        }

        if (isset($res['sendstate']) && isset($res['sendmsgstate'])) {
            $rs['sendstate'] = $res['sendstate'];
            $rs['sendmsgstate'] = $res['sendmsgstate'];
        }
        //if(issendsms==1)sendsms($order['guestmobile'], $rs['sendmsg']);
        $rs['senduid'] = $para['uid'];
        $rs['sendcomid'] = $para['comid'];

        $rs['sendtimeint'] = time();
        $rs['sendip'] = $GLOBALS['main']->getip();
        //$dir =  sysdir.'_sms/log/'.time().'.txt';
        //$GLOBALS['we']->write_file($dir , implode(',',$rs));

        /* 添加日志 */
        $this->insertsendlog($rs);

        return true;
    }

    function getpayrs() {
        return true;
    }

    /* 短信模板
     * type : 短信类型
     *      */

    function getsendmsg($type) {
        $ejialogo = '【e家神灯】';
        $ejiatel = '详询：4006-992-996';
        $ejiatel_sim = '4006-992-996';
//        $ejianick = '亲爱的e家人，';

        $msg = array(
            '1' => '注册会员，发激活码',
//            '2' => '注册会员成功',
//            '3' => '支付成功',
//            '4' => '修改定单成功',
//            '5' => '进行续住操作',
//            '6' => '续住成功',
//            '7' => '办理入住成功',
//            '8' => '办理离店成功',
//            '9' => '取消定单成功',
//            '10' => '充值成功',
//            '11' => '酒店最晚入住时间前2小时未入住提示',
//            '12' => '距离店时间前2小时提示',
            '13' => '找回密码发送验证码',
//            '14' => '入住码发送',
//            '15' => '支付单修改新入住码发送',
//            '16' => '开启支付密码',
//            '17' => '修改支付密码',
//            '18' => '找回支付密码',
//            '19' => '半小时未支付自动取消定单',
//            '20' => 'e家预定平台预定体验',
//            '21' => '修改邮箱',
//            '22' => '办理离店成功（首单）',
//            '23' => '取消定单有退款',
//            '24' => '修改确认单',
//            '25' => '终端机验证',
//            '26' => '终端机礼包',
//            '27' => '终端机预定',
//            '28' => '离店有退款',
//            '29' => '终端机支付订单成功（身份证未刷齐）',
//            '30' => '终端机注册（无礼包）',
//            '31' => '预付未到定单过期完成（有退款）',
//            '32' => '预付未到定单过期完成（无退款）',
//            '33' => '关闭定单交易',
//            '34' => '终端机首单入住'
            '35' => '在线调查验证',
            '36' => '单片机服务端掉线',
             '37' => '酒店已运行的设备倾斜'
        );
        if (array_key_exists($type, $msg)) {
            $rs ['sendtype'] = $type;
            $rs ['sendtypename'] = $msg [$type];
        } else {
            $GLOBALS['errmsg'][] = ( '未知类型的短信，请重新发' );
            return false;
        }


        switch ($type) {
            case 1 :
                $rs ['sendmsg'] = $ejialogo . '验证码:{$activecode}，您正在注册e家神灯，请妥善保管，' . $ejiatel;
                break;
//            case 2 :
//                $rs ['sendmsg'] = $ejialogo . '欢迎您注册成为e家人，我们将为您提供尊贵、快捷的优质服务。网址：ejiayuding.com。' . $ejiatel;
//                break;
//            case 3 :
//                //$rs ['sendmsg'] =  $ejialogo.$ejianick .  '您已成功支付'.$order['id'].'定单（'.$order['roomname'].(($order['mydate2']-$order['mydate1'])/86400).'天）'.$order['payprice'].'元。我们将为您保留到'.date('Y-m-d',$order['mydate1']+86400).'的'. $delaytime.'点，祝您生活愉快。' . $ejiatel;
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功支付' . $order['id'] . '定单（' . $order['roomname'] . (($order['mydate2'] - $order['mydate1']) / 86400) . '天）' . $order['payprice'] . '元。我们将为您保留到' . date('Y-m-d', $order['mydate1'] + 86400) . '的' . $delaytime . '点，祝您生活愉快。' . $ejiatel;
//                break;
//            case 4 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您的定单（定单ID：' . $order['id'] . '）已于' . date('Y') . '年' . date('m') . '月' . date('d') . '日成功修改。我们将为您保留到' . date('Y', $order['mydate1'] + 86400) . '年' . date('m', $order['mydate1'] + 86400) . '月' . date('d', $order['mydate1'] + 86400) . '日' . $delaytime . '点。' . $ejiatel;
//                break;
//            case 5 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您续住' . $order['id'] . '的动态密码为：' . $order['continuelivecode'] . '，30分钟内有效，请妥善保管。' . $ejiatel;
//                break;
//            case 6 :
//                //$rs ['sendmsg'] =  $ejianick .  '您的'.$order['id'].'定单已成功续住，现离店时间'.date('Y-m-d',$order['mydate2']).'，我们用'.(($order['mydate2']-$order['mydate1'])/86400).'天的陪伴让您感受家一样的温暖。' . $ejiatel.$ejialogo;
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功支付' . $order['id'] . '定单的续住单' . $rs['myvalueout'] . '元，现离店时间' . date('Y-m-d', $order['mydate2']) . '，我们用' . (($order['mydate2'] - $order['mydate1']) / 86400) . '天的陪伴让您感受家一样的温暖。' . $ejiatel;
//                break;
//            case 7 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功入住' . $order['hotelname'] . '的' . $order['roomname'] . '，定单号为：' . $order['id'] . '，请您妥善保管房卡和客单，根据《酒店业治安管理条例》规定，请您配合前往前台扫描有效身份证件。祝您生活愉快！' . $ejiatel;
//                break;
//            case 8 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功办理' . $order['id'] . '定单的离店手续，祝您旅途愉快，期待您的下次光临。' . $ejiatel;
//                break;
//            case 9 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您的定单（定单ID：' . $order['id'] . '）已于' . date('Y') . '年' . date('m') . '月' . date('d') . '日成功取消。' . $ejiatel;
//                break;
//            case 10 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '感谢您于' . date('m') . '月' . date('d') . '日充值' . $order['totalfee'] . '元，当前余额' . $order['canuse'] . '元。' . $ejiatel;
//                break;
//            case 11 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '距您在XX酒店（定单ID：' . $order['id'] . '）最晚办理入住时间还有两个小时，我们恭候您的到来。如需取消定单，请及时办理，以免超时扣费哦。' . $ejiatel;
//                break;
//            case 12 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '距您XX酒店XX房间（定单ID：' . $order['id'] . '）的离店时间已不足两个小时。如需继续入住，请及时办理。' . $ejiatel;
//                break;
            case 13 :
                $rs ['sendmsg'] = $ejialogo . '验证码：{$activecode}，您正在找回密码，请妥善保管，' . $ejiatel;
                break;
//            case 14 :
//                $livecode = explode(',', $order['livecode']);
//                //stop(count($livecode));
//                if (count($livecode) == 1) {
//                    $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功支付' . $order['id'] . '定单' . $order['payprice'] . '元。' . $order['roomname'] . '的入住验证码为：' . $order['livecode'] . '。（请携带身份证）我们将为您保留到' . date('Y-m-d', $order['mydate1'] + 86400) . '的' . $delaytime . ':00，祝您生活愉快。' . $ejiatel;
//                } else {
//                    $livex = '';
//                    foreach ($livecode as $k => $v) {
//                        switch ($k) {
//                            case 0:
//                                $k = '一';
//                                break;
//                            case 1:
//                                $k = '二';
//                                break;
//                            case 2:
//                                $k = '三';
//                                break;
//                            case 3:
//                                $k = '四';
//                                break;
//                            case 4:
//                                $k = '五';
//                                break;
//                            default:
//                                $k = '六';
//                                break;
//                        }
//                        $livex .= '第' . $k . '入住人的入住验证码为：' . $v . '。';
//                    }
//                    $rs['sendmsg'] = $ejialogo . $ejianick . '您已成功支付' . $order['id'] . '定单' . $order['payprice'] . '元。房型为：' . $order['roomname'] . '，' . $livex . '（请携带身份证）我们将为您保留到' . date('Y-m-d', $order['mydate1'] + 86400) . '的' . $delaytime . ':00，祝您生活愉快。' . $ejiatel;
//                }
//                break;
//            case 15 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您的定单（定单ID：' . $order['id'] . '）已于' . date('Y') . '年' . date('m') . '月' . date('d') . '日成功修改' . $order['roomname'] . '的新增入住验证码为：' . $order['livecode'] . '，我们将为你保留到' . date('Y', $order['mydate1'] + 86400) . '年' . date('m', $order['mydate1'] + 86400) . '月' . date('d', $order['mydate1'] + 86400) . '日' . $delaytime . '点。' . $ejiatel;
//                break;
//            case 16 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您正在开启支付密码，验证码为' . $order['activecode'] . '，30分钟内有效，请妥善保管。如非本人操作，请及时与客服联系。' . $ejiatel;
//                break;
//            case 17 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您正在修改支付密码，验证码为' . $order['activecode'] . '，30分钟内有效，请妥善保管。如非本人操作，请及时与客服联系。' . $ejiatel;
//                break;
//            case 18 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您正在申请找回支付密码，验证码为' . $order['activecode'] . '，30分钟内有效，请妥善保管。如非本人操作，请及时与客服联系。' . $ejiatel;
//                break;
//            case 19 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '由于您半小时内未支付定单，您的定单（定单ID：' . $order['id'] . '）已于' . date('Y') . '年' . date('m') . '月' . date('d') . '日被取消。' . $ejiatel;
//                break;
//            case 20 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '感谢您体验e家预定智能入住平台。' . $order['roomname'] . '的入住验证码为：' . $order['livecode'] . '，祝您生活愉快。' . $ejiatel;
//                break;
//            case 21 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您正在申请邮箱修改，验证码为' . $order['activecode'] . '，30分钟内有效，请妥善保管。如非本人操作，请及时与客服联系。' . $ejiatel;
//                break;
//            case 22 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功完成' . $order['id'] . '定单，恭喜您获得了价值70元的代金券，请查看您的账户，期待您的下次光临。' . $ejiatel;
//                break;
//            case 23 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您的定单（定单ID：' . $order['id'] . '）已于' . date('Y') . '年' . date('m') . '月' . date('d') . '日成功取消，定单退款' . $order['backprice'] . '元已抵达您的会员账户，请您查阅。' . $ejiatel;
//                break;
//            case 24 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您的定单（定单ID：' . $order['id'] . '）已于' . date('Y') . '年' . date('m') . '月' . date('d') . '日成功修改。请在' . date('H', $order['stimeint'] + 1800) . '时' . date('i', $order['stimeint'] + 1800) . '分前支付定单，感谢您的使用。' . $ejiatel;
//                break;
//            case 25 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您预定' . $order['id'] . '的动态密码为：' . $order['validcode'] . '，30分钟内有效，请妥善保管，' . $ejiatel;
//                break;
//            case 26 :
//                $rs ['sendmsg'] = $ejialogo . '感谢您使用e住通，您可通过扫描凭条二维码或进入网站www.ejiayuding.com，使用您的手机号码' . $order['guestmobile'] . '和临时动态密码：' . $order['password'] . '登录，领取您的价值200元大礼包，我们将带给您“想住就住，想走就走”的全新入住体验。' . $ejiatel;
//                break;
//            case 27 :
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您正在终端机进行预定，验证码为' . $order['validcode'] . '，30分钟内有效，请妥善保管。' . $ejiatel;
//                break;
//            case 28:
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功办理' . $order['id'] . '定单的离店手续，定单退款' . $order['myvalue'] . '元已抵达您的会员账户余额，请您查阅期待您的下次光临。如需退款到银行卡，请拨打客服电话：' . $ejiatel_sim;
//                break;
//            case 29:
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您' . $order['id'] . '定单的其他入住人，可通过验证入住进行办理，入住验证码为：' . $order['livecode'] . '。' . $ejiatel;
//                break;
//            case 30:
//                $rs ['sendmsg'] = $ejialogo . '感谢您使用e住通，您可通过扫描凭条二维码或进入网站www.ejiayuding.com，使用您的手机号码' . $order['guestmobile'] . '和临时动态密码：' . $order['password'] . '登录，我们将带给您“想住就住，想走就走”的全新入住体验。' . $ejiatel;
//                break;
//            case 31:
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '由于您的定单（定单ID：' . $order['id'] . '）已过期，定单已自动结束并结算，定单退款' . $order['backprice'] . '元已抵达您的会员账户余额，请您查阅，期待您的下次光临。如需退款到银行卡，请拨打客服电话：' . $ejiatel_sim;
//                break;
//            case 32:
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '由于您的定单（定单ID：' . $order['id'] . '）已过期，定单已自动结束并结算，请您查阅，期待您的下次光临。' . $ejiatel;
//                break;
//            case 33:
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '由于您的定单' . $order['id'] . '已关闭交易，您此次支付的' . $order['payprice'] . '元房费已退回到您的e家预定账户余额中，您可重新预定房型并进行余额支付，如需申请退款，请拨打客服电话：' . $ejiatel_sim;
//                break;
//            case 34:
//                $rs ['sendmsg'] = $ejialogo . $ejianick . '您已成功入住' . $order['hotelname'] . '的' . $order['roomname'] . '，定单号为:' . $order['id'] . '。记得妥善保管房卡和客单。当然，根据《酒店业治安管理条例》规定，需要您配合在前台扫描有效身份证件,谢谢您的理解！e家人私享：这次您通过智能前台办理入住，让小e深深记住了您,为表诚意，您下次使用e家预定APP预定酒店，可尊享房价立减' . firstorderfavor . '元特惠礼遇！' . $ejiatel;
//                break;
//            default :
//                $rs['sendmsg'] = null;
//                break;
            case 35 :
                $rs ['sendmsg'] = $ejialogo . '验证码：{$activecode}，您正在进行问卷调查，请妥善保管，' . $ejiatel;
                break;
            case 36 :
                $rs ['sendmsg'] = $ejialogo . '呜呜~~主人我掉线了，赶快重启server.exe，拯救我吧';
                break;  
               case 37:
                $rs ['sendmsg'] = $ejialogo . '呜呜~~主人我是{$activecode}，我的机体已经倾斜了，赶快救救我吧';
                break;      
        }
        if ($rs['sendmsg']) {

            return $rs['sendmsg'];
        } else {
            $GLOBALS['errmsg'][] = ( '未知的短信内容，请重新发' );
            return false;
        }
    }

    function Post($data, $target) {
        $url_info = parse_url;
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        //$httpheader .= "Connection:Keep-Alive\r\n\r\n";
        $httpheader .= $data;
        $gets = "";
        $fd = fsockopen($url_info['host'], 80);
        if ($fd) {
            fwrite($fd, $httpheader);
            while (!feof($fd)) {
                $gets .= fread($fd, 128);
            }
            fclose($fd);
        } else {
            $GLOBALS['errmsg'][] = ( '打开短信通道有问题！');
            return false;
        }
        return $gets;
    }

    function getsendmemmobile($a_order) {

        if (isset($a_order['uid']) && $a_order['uid'] > 0) {
            /* 提取定单中的用户信息 */
            $sql = 'select * from `' . sheet . '_user` where id=' . $a_order['uid'];
            $a_user = $GLOBALS['we']->exeone($sql);
            if (false == $a_user) {
                $GLOBALS['errmsg'][] = ('定单用户不存在');
                return false;
            }
            return $a_user['u_mobile'];
        }
        return false;
    }

    function gettime($hotelid) {
        $sql = 'select delaytime1 from`' . sheet . '_hotel` where id=' . $hotelid;
        $res = $GLOBALS['we']->exeone($sql);
        return $res['delaytime1'];
    }

    /*
     * $target = "http://cf.lmobile.cn/submitdata/Service.asmx/g_Submit"; //替换成自己的测试账号,参数顺序和wenservice对应 //$post_data = 'sname=dlsdcs00&spwd=g0k0lGoS&scorpid=&sprdid=1012818&sdst=13043272481&smsg='.rawurlencode('尊敬的用户：http://weibo.com测试123456是您本次的短信验证码，5分钟内有效.【百分信息】'); $post_data = "sname=dlssbw04&spwd=bw123456&scorpid=&sprdid=1012818&sdst=15522000677&smsg=".rawurlencode("尊敬的用户：http://t.cn/R7zSlZd，您本次的短信验证码是123456【汇川】"); //$binarydata = pack("A", $post_data); echo $gets = Post($post_data, $target);
     */
// 请自己解析$gets字符串并实现自己的逻辑
// <State>0</State>表示成功,其它的参考文档
}

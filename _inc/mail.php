<?php
require_once('mail/class.phpmailer.php');

class Cls_Mail{
	function smtp_mail( $sendto_email, $subject, $body){    
		$mail = new PHPMailer();    
		$mail->IsSMTP();                  // send via SMTP    

		$mail->SMTPAuth = true;           // turn on SMTP authentication    
		$mail->Host = MailSmtp;   // SMTP servers    
		$mail->Username = MailServerUserName;     // SMTP username  注意：普通邮件认证不需要加 @域名    
		$mail->Password = MailServerPassword; // SMTP password    
		$mail->From = MailFrom;      // 发件人邮箱    
		$mail->FromName =  MailFromName;  // 发件人    
	  
		$mail->CharSet = 'UTF-8';   // 这里指定字符集！    
		$mail->Encoding = 'base64';    
		$mail->AddAddress($sendto_email, "tousername");  // 收件人邮箱和姓名
		$mail->IsHTML(false);  // send as HTML    
		// 邮件主题    
		$mail->Subject = $subject;    
		// 邮件内容    
		$mail->Body = $body;                                                                          
		$mail->AltBody ="text/html";

		$rs['sendmail'] = $sendto_email;
		$rs['sendsubject'] = $subject;
		$rs['sendmsg'] = $body;
		$rs['sendip'] = $GLOBALS['we']->getip();
		$rs['sendtime'] = date('Y-m-d H:i:s',time());
		$rs['sendtimeint'] = time();
		$rs['senduid'] = 1;
		$rs['sendduid'] = $GLOBALS['we']->user['id']?:1;
		$rs['frommail'] = MailFrom;
		$rs['fromname'] = MailFromName;
		if(!$mail->Send())   
		{   
			return FALSE;
			// echo '邮件发送有误 <p>';    
			// echo '邮件错误信息: ' . $mail->ErrorInfo;    
			// exit;    
		}    
		else {
			$this->insert_mail_log($rs);
			//echo '邮件发送成功!<br />';    
			return TRUE;
		}
	}
	function insert_mail_log($rs){
        $GLOBALS ['we']->pdo->insert ( sheet . '_sendmail', $rs );
	}

}
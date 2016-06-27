<?php

/* 在线调查公用数据 */

class cls_form {

    function __construct() {
        $this->pdo =& $GLOBALS['pdo'];
        $this->main =& $GLOBALS['main'];
        
        /* 广告公司 */
        $a = array(
            'renshou' => array('title' => '中国人寿'),
            'sunyilin' => array('title' => '孙浥林调查组')
        );
        $this->adcom = $a;

        /* 调查表状态 */
        $a = array(
            'new' => '新问卷',
            'doing' => '正在进行',
            'stop' => '停止',
            'done' => '已结束'
        );
        $this->formtatus = $a;
    }

    /*按id提取调查*/
    function getformbyid($formid, $mystatus=null){
        $sql = 'select * from `' . sh . '_form` where 1 ';
        
        if(null != $mystatus){
            $sql .= ' and mystatus="doing" '; //只能填进行中的调查
        }
        
        $sql .= ' and id =:formid';
        $sql .= ' and stimeint<' . time();
        
        $result = $this->pdo->fetchOne($sql, Array(':formid' => $formid));
        
        return $result;
    }
    
    /*查是不是答过这个调查了*/
    function hasdoneform($formid){
        $sql = 'select count(*) from `' . sh . '_formdolist` where 1 ';
        $sql .= ' and formid=:formid';
        $sql .= ' and uid=:uid ';
        
        $para[':uid'] = $this->main->user['id'];
        $para[':formid'] = $formid;
        
        $count = $this->pdo->counts($sql, $para );
        
        if ($count > 0) {
			$this->ckerr('这个调查已答过了。');
            return true; 
        }else{
            return false;
        }
    }
	
				
}

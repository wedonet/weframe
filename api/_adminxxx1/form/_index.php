<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once syspath . '_inc/cls_form.php';

require_once AdminApiPath . '_main.php'; //本模块数据

/* 返回用户组 */

class myapi extends cls_api {

    function __construct() {
        parent::__construct();

        $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }

        switch ($this->act) {
            case '':
                $this->pagemain();
                $this->output();
                break;
            case 'nsave':
                $this->nsave();
                $this->output();
                break;
            case 'esave':
                $this->esave();
                $this->output();
                break;
            case 'doing':
            case 'stop':
            case 'delete':
                $this->operate();
                $this->output();
                break;
            case 'edit':
                $this->getform();
                $this->output();
                break;
        }
    }

    function pagemain() {
//               $suid = $this->main->rqid('id');
        $c_form = new cls_form();
        /* 调取当前时间 */
        
        $sql='update `' . sh . '_form`  set mystatus="done" where overtime < '.time();
        $this->pdo->doSql($sql);
       
        /* 提取这个调查 */
        $sql = 'select form.* ,users.u_nick as unick ';
        $sql .= ' from ' . sh . '_form as form left join ' . sh . '_user as users';
        $sql .= ' on form.suid=users.id ';
        $sql .= ' order by form.id desc ';
        $result = $this->main->exers($sql);

        $i = 0;
        foreach ($result['rs'] as $v) {
            $result['rs'][$i]['mystatusname'] = $c_form->formtatus[$v['mystatus']];
            $i++;
        }
        $this->j['list'] = $result;
    }
    

    function nsave() {
        $c_form = new cls_form();

        //$this->paralog();
        $this->main->posttype = 'post';
        $ic = $this->main->request('ic', 'IC', 1, 10, 'char', 'invalid');
        $aic = $this->main->request('aic', '广告商IC', 1, 10, 'char', 'invalid');
        $title = $this->main->request('title', '名称', 2, 50, 'char', 'encode');
        $readme = $this->main->request('readme', '说明', 2, 50, 'char', 'invalid', false);
        $myvalue = $this->main->request('myvalue', '返款额', 0.01, 100, 'num');
        $mycount = $this->main->request('mycount', '数量', 1, 1000000, 'int');
        $questioncount = $this->main->request('questioncount', '题数', 1, 1000000, 'int');
        $plantime = $this->main->request('plantime', '预计答题时间', 1, 1000000, 'int');
        $overtime = $this->main->request('overtime', '到期时间', 8, 20, 'date');

        $this->ckerr();
        /* 调取当前时间 */
        $d = date('y-m-d');

        /* 检测有没有这个ic */
        if ($this->main->hasic(sh . '_form', 0, $ic)) {
            $this->ckerr('已经有这个IC了，请重新输入');
            return false;
        }

        /* 检测有没有这个广告商 */
        if (!array_key_exists($aic, $c_form->adcom)) {
            $this->ckerr('没找到这个广告商');
            return false;
        }

        /* 检测返款金额 */
        if (!$this->main->is2price($myvalue)) {
            $this->ckerr('返款金额小数点后不能大于两位');
            return false;
        }
        /* 检测到期时间 */
        if (strtotime($overtime) <= strtotime($d)) {
            $this->ckerr('到期时间应晚于当天');
            return false;
        }

        /* 入库 */
        $currenttime = time();

        $rs['ic'] = $ic;
        $rs['aic'] = $aic;
        $rs['title'] = $title;
        $rs['readme'] = $readme;
        $rs['myvalue'] = $myvalue * 100;
        $rs['mycount'] = $mycount;
        $rs['questioncount'] = $questioncount;
        $rs['plantime'] = $plantime;
        $rs['suid'] = $this->main->user['id'];

        $rs['stime'] = date('Y-m-d H:i:s', $currenttime);
        $rs['stimeint'] = $currenttime;
        $rs['overtime'] = strtotime($overtime);
        $rs['mystatus'] = 'new';
        

        $this->pdo->insert(sh . '_form', $rs);

        $this->j['success'] = 'y';
    }

    function esave() {
        $we = & $GLOBALS['main'];

        $c_form = new cls_form();

        $id = $we->rfid();

        $we->posttype = 'post';

        $rs['ic'] = $we->request('ic', 'IC', 1, 10, 'char', 'invalid');
        $rs['title'] = $we->request('title', '名称', 2, 50, 'char', 'encode');
        $rs['readme'] = $we->request('readme', '说明', 2, 50, 'char', 'invalid', false);
        $rs['myvalue'] = $we->request('myvalue', '返款额', 0.01, 100, 'num');
        $rs['mycount'] = $we->request('mycount', '数量', 1, 1000000, 'int');
        $rs['questioncount'] = $we->request('questioncount', '题数', 1, 1000000, 'int');
        $rs['plantime'] = $we->request('plantime', '预计答题时间', 1, 1000000, 'int');
        $rs['overtime'] = $we->request('overtime', '到期时间', 8, 20, 'date');

       if( !$this->ckerr()){
           return false;
       }
             
        /* 调取当前时间 */
        $d = date('y-m-d');

        /* 检测有没有这个ic */
        if ($this->main->hasname(sh . '_form', 'ic', $rs['ic'], $id)) {
            $this->ckerr('已经有这个IC了，请重新输入', 'ic');
        }


        /* 检测返款金额 */

        if (!$this->main->is2price($rs['myvalue'])) {
            $this->ckerr('返款金额小数点后不能大于两位');
            return false;
        }
        /* 检测到期时间 */
        if (strtotime($rs['overtime']) <= strtotime($d)) {
            $this->ckerr('到期时间应晚于当天');
            return false;
        }
         $rs['overtime'] = strtotime($rs['overtime']);
         $rs['myvalue'] = $rs['myvalue'] * 100;
         
        $this->pdo->update(sh . '_form', $rs, ' id=:id', Array(':id' => $id));

        $this->j['success'] = 'y';
    }
    

    function getform() {
        $id = $this->main->rid();

        $sql = 'select * from `' . sh . '_form` where 1 ';
        $sql .= ' and id=:id';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $this->j['data'] = $result;
    }

    function operate() {
        $id = $this->main->rqid('id');

        /* 提取这个调查 */

        $sql = 'select * from `' . sh . '_form` where 1';
        $sql .= ' and id=' . $id;

        $a_form = $this->pdo->fetchOne($sql);

        if (false === $a_form) {
            $this->ckerr('没找到这个调查');
            return false;
        }


        switch ($this->act) {
            case 'doing':
                /* 未到期的才能设成运行状态，暂时没检测 */

                if ('doing' == $a_form['mystatus']) {
                    $this->ckerr('这个调查已经是运行状态了');
                    return false;
                }
                /* 未到期的才能设成运行状态，暂时没检测 */

                if ('done' == $a_form['mystatus']) {
                    $this->ckerr('这个调查已经结束了');
                    return false;
                }
                

                $sql = 'update `' . sh . '_form` set mystatus="doing" where id=' . $id;

                $this->pdo->doSql($sql);

                break;
            case 'stop':
                /* 未到期的才能设成运行状态，暂时没检测 */

                if ('stop' == $a_form['mystatus']) {
                    $this->ckerr('这个调查已经是停止状态了');
                    return false;
                }
                /* 未到期的才能设成运行状态，暂时没检测 */

                if ('done' == $a_form['mystatus']) {
                    $this->ckerr('这个调查已经结束了');
                    return false;
                }
                

                $sql = 'update `' . sh . '_form` set mystatus="stop" where id=' . $id;

                $this->pdo->doSql($sql);

                break;				
			
            case 'delete':
                /*答过了就不能删除*/
                $sql = 'select count(*) from `'.sh.'_formdolist` where 1 ';
                $sql .= ' and formid=:formid';
                $para[':formid'] = $id;
                $counts = $this->pdo->counts($sql, $para);
                if($counts>0){
                    $this->ckerr('不能删除已经回答过的调查!');
                    return false;
                }
                
                $sql = 'delete from `' . sh . '_form` where id=' . $id;

                $this->pdo->doSql($sql);

                break;
        }
        

        $this->j['success'] = 'y';
    }

}

$myapi = new myapi();
unset($myapi);

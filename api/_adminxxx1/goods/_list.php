<?php

/* 平台商品接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once AdminApiPath . '_main.php';

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
                $this->mylist();
                $this->output();
                break;
            case 'creat':
                $this->getdata();
                $this->output();
                break;
            case 'edit':
                $this->getdata();
                $this->output();
                break;
            case 'admin': //管理用户
                $this->getdata();
                $this->output();
                break;


            case 'nsave':
                $this->savenew();
                $this->output();
                break;
            case 'esave':
                $this->esave();
                $this->output();
                break;

            case 'del':
                $this->del();
                $this->output();
                break;
        }
    }

    /* 用户列表 */

    function mylist() {
        /* 接收参数 */
        $this->posttype = 'get';
        $title = $this->main->request('title', '商品名称', 1, 50, 'char', '', false);

        if (!$this->ckerr()) {
            return false;
        }

        /* 传回前端 */
        $this->j['search']['title'] = $title;




        /* 提取所有商品 */
        $para = Array();
        $sql = 'select * from `' . sh . '_goods` where 1 ';
        
        if ('' != $title) {

            $sql .= ' and title like :title';
            $para[':title'] = '%' . $title . '%';   
            
        }

        $sql .= ' order by id desc ';

        $a_goods = $this->main->exers($sql, $para);

        /* 提取所有单品，供显示在组合品里 */
        $sql = 'select id,title,preimg from `' . sh . '_goods` where 1 ';
        $sql .= ' and isgroup=0 ';
        $result = $this->pdo->fetchAll($sql);
        /* 单口以id做为索引 */
        $a_singlegoods = array();
        foreach ($result as $v) {
            $a_singlegoods[$v['id']] = $v;
        }

        $this->j['list'] = $a_goods;
        $this->j['listsingle'] = & $a_singlegoods;
    }

    function savenew() {
        $we = & $GLOBALS['main'];

        $we->posttype = 'post';

        $rs['title'] = $we->request('title', '名称', 1, 128, 'char', 'encode');
        $rs['ic'] = $we->request('ic', '商品ic', 1, 20, 'char', '');
        $rs['preimg'] = $we->request('preimg', '商品图片', 10, 255, 'char');
        //$rs['bigimg'] = $we->request('bigimg', '大图', 10, 255, 'char');
        $rs['readme'] = $we->request('readme', '简介', 2, 255, 'char', 'encode');
        $rs['isgroup'] = $we->request('isgroup', '属性', 0, 1, 'int');
        $rs['mygroup'] = $we->request('mygroup', '组合品', 0, 50, 'char', '', false);

        $rs['content'] = $we->request('content', '描述', 2, 255000, 'char');

        if (!$this->ckerr()) {
            return;
        }

        $rs['mygroup'] = $this->getmygroup($rs); //获取组合品json字串，并检测是否组合品

        if (!$this->ckerr()) {
            return;
        }

        $rs['bigimg'] = $this->getbigimg($rs['preimg']); //由小图路径分析出大图路径
        //检测商品名称
        if ($this->main->hasname(sh . '_goods', 'title', $rs['title'])) {
            $this->ckerr('此名称已经存在，请重新输入', 'title');
        }

        /* 检测重复ic */
        if ($this->main->hasic(sh . '_goods', -1, $rs['ic'])) {
            $this->ckerr('ic重复,请重新输入', 'ic');
        };

        $rs['comid'] = 0;         //平台商品

        $this->pdo->insert(sh . '_goods', $rs);

        $this->j['success'] = 'y';
    }

    function esave() {
        $we = & $GLOBALS['main'];

        $id = $we->rfid();

        $we->posttype = 'post';

        $rs['title'] = $we->request('title', '名称', 1, 128, 'char', 'encode');
        $rs['ic'] = $we->request('ic', '商品ic', 1, 20, 'char', '');
        $rs['preimg'] = $we->request('preimg', '商品图片', 10, 255, 'char');
        //$rs['bigimg'] = $we->request('bigimg', '大图', 10, 255, 'char');
        $rs['readme'] = $we->request('readme', '简介', 2, 255, 'char', 'encode');

        $rs['isgroup'] = $we->request('isgroup', '属性', 0, 1, 'int');
        $rs['mygroup'] = $we->request('mygroup', '组合品', 0, 50, 'char','', false);

        $rs['content'] = $we->request('content', '描述', 2, 255000, 'char');
        
        if (!$this->ckerr()) {
            return;
        }

        /* 检测组合品是存在 */
        $rs['mygroup'] = $this->getmygroup($rs); //获取组合品json字串，并检测是否组合品

        if (!$this->ckerr()) {
            return;
        }



        $rs['bigimg'] = $this->getbigimg($rs['preimg']); //由小图路径分析出大图路径
        //检测商品名称
        if ($this->main->hasname(sh . '_goods', 'title', $rs['title'], $id)) {
            $this->ckerr('此名称已经存在，请重新输入', 'title');
        }

        /* 检测重复ic */
        if ($this->main->hasic(sh . '_goods', $id, $rs['ic'])) {
            $this->ckerr('ic重复,请重新输入', 'ic');
        }

        $this->pdo->update(sh . '_goods', $rs, 'id=:id', Array(':id' => $id));


        /* 更新其它地调这个商品ic的地 */


        $this->j['success'] = 'y';
    }

    function del() {
        $id = $this->main->rqid();

        /* 检测是否有柜门在卖 */
       // $sql = 'select * from `' . sh . '_door` where goodsid=:goodsid';
        $sql = 'select * from `' . sh . '_comgoods` where goodsid=:id';
//print_r($sql);die;
        $result = $this->pdo->fetchAll($sql, Array(':id' => $id));

        if (false !=$result) {
            $s = '';
            foreach ($result as $v) {
                $s .= '店铺ID:' . $v['comid']. '<br />';
            }
            //print_r($s);die;
            $this->ckerr('已经有店铺在出售这件商品了，请先从店铺删除此商品<br />'. $s);
        }

        /* 检测是否有组合品包含这个单品 */
        $sql = 'select id from `' . sh . '_goods` where 1 ';
        $sql .= ' and mygroup is not null ';
        $sql .= ' and mygroup like :id';


        $result = $this->pdo->fetchAll($sql, Array(':id' => '%"id":"' . $id . '"%'));

        if (false != $result) {
            $wrongids = array_column($result, 'id');
            $this->ckerr(join(',', $wrongids) . '包含这个商品，请先修改组合品再删除');
            return false;
        }




        $sql = 'delete from `' . sh . '_goods` where 1 ';
        $sql .= ' and id=:id ';

        $this->pdo->doSql($sql, Array(':id' => $id));

        $this->j['success'] = 'y';
    }

    /* 取店铺信息 */

    function getdata() {
        $id = $this->main->rqid();

        $sql = 'select * from `' . sh . '_goods` where id=:id ';

        $result = $this->pdo->fetchOne($sql, Array(':id' => $id));

        $this->j['data'] = $result;
    }

    function getbigimg($s) {
        /* 是缩略图 */
        if (strpos($s, '/thumb/') > 0) {
            return str_replace('thumb/', '', $s);
        } else {
            return $s;
        }
    }

    /* 检测绷带合品，并生成json字串
     * 原格式 ： 1*2，200*1， 格式化生成json字串
     *  */

    function getmygroup($res) {
        $mygroup = Array();

        /* 如果是组合品，必须填组合品id */

        if ('0' == $res['isgroup']) {
            return '';
        }

        if ('' == $res['mygroup']) {
            $GLOBALS['errmsg'][] = '请填写组合品';
            return false;
        }
        $a = explode(',', $res['mygroup']);

        /* 循环每组商品，检测输入格式 */
        $i = 0;
        foreach ($a as $v) {
            if (false === strpos($v, '*')) {
                $GLOBALS['errmsg'][] = '组合品格式错误!';
                return false;
            }

            /* 这组商品 */
            $thisgoods = explode('*', $v);

            /* 有星号检测两边是否数字 */
            if (!$this->main->isint($thisgoods[0]) OR !$this->main->isint($thisgoods[1])) {
                $GLOBALS['errmsg'][] = '组合品格式错误!';
                return false;
            }


            $mygroup[$i]['id'] = $thisgoods[0];
            $mygroup[$i]['count'] = $thisgoods[1];

            $i++;
        }


        /* 检测商品是否存在 */
        foreach ($mygroup as $v) {
            $sql = 'select count(*) from `' . sh . '_goods` where 1 ';
            $sql .= ' and id=' . $v['id'];

            $counts = $this->pdo->counts($sql);

            if (0 == $counts) {
                $GLOBALS['errmsg'][] = '组合品不存在,id:' . $v['id'];
                return false;
            }
        }

        return json_encode($mygroup);
    }

}

$myapi = new myapi();
unset($sys_admin_user);

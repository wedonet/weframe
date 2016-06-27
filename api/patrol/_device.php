<?php

/* 列出所有格子的商品情况 */
require_once( __DIR__ . '/../../global.php');
require_once syspath . '_inc/cls_api.php';

require_once ApiPath . 'patrol/_main.php'; //补货页通用数据

//require_once '_power.php'; //补货页通用数据

class myapi extends cls_api {

    function __construct() {
        parent::__construct();
        
        $this->modulemain = new cls_modulemain();/* 检测权限类 */
        
        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
        
       /*==============================*/
		/*什么情况下必须返回json格式*/

        $jsonact = array('json'
            , 'esave'
            , 'nsave'
            , 'loginin'
            , 'loginout'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }


        switch ($this->main->ract()) {
              case '':
            $this->main();
            $this->output(); 
            break;
        }
    }
   function main() {
      $placeid = $this->main->rqid('placeid');
      $deviceid = $this->main->rqid('deviceid');

      /* 提取位置 */
      $sql = 'select * from `' . sh . '_place` where 1 ';
      $sql .= ' and id=:placeid ';

      $result = $this->pdo->fetchOne($sql, Array(':placeid' => $placeid));

      $this->j['currentplace'] = $result;

      /* 取格子+商品信息 */
      $sql = 'select *,goods.title as goodstitle ';
      $sql .= ' ,comgoods.price ';
      $sql .= ' ,door.title as id ';
      $sql .= ' from `' . sh . '_door` as door ';
      $sql .= ' left join `' . sh . '_goods` as goods on door.goodsid=goods.id ';
      $sql .= ' left join `' . sh . '_comgoods` as comgoods on door.comgoodsid=comgoods.id ';
      $sql .= ' where 1 ';
      $sql .= ' and deviceid=:deviceid';
      $sql .= ' order by door.id asc ';

      $result = $this->pdo->fetchAll($sql, Array(':deviceid' => $deviceid));

      $GLOBALS['j']['list'] = $result;

   }

}

  
$myapi = new myapi();
unset($myapi);
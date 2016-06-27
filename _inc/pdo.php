<?php

/**
 * 功能:数据库操作类     
 * 作者:孙浥林 参考 phpox     
 * 日期:Tue Aug 14 08:46:27 CST 2007     
 */
class Cls_Pdo {

    private static $instance;
    //public $dsn;
    // public $dbuser;
    //public $dbpass;
    public $sth;
    public $dbh;
    public $isconnedted; //是否已连接数据库
    public $ATTR_PERSISTENT = false; //是否长连接
    public $sqlquerynum = 0; //执行查询次数

    function __construct() {
        
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new include_database();
        }
        return self::$instance;
    }

    //连接数据库       
    private function connect() {
        $DB_HOST = $GLOBALS['config']['DbHost'];
        $DB_PORT = $GLOBALS['config']['Dbport'];
        $DB_NAME = $GLOBALS['config']['Dbname'];
        $DB_USER = $GLOBALS['config']['Dbuser'];
        $DB_PASS = $GLOBALS['config']['Dbpass'];

        $this->dsn = 'mysql:host=' . $DB_HOST . ';port=' . $DB_PORT . ';dbname=' . $DB_NAME;
        //$this->dbuser = $DB_USER;
        //$this->dbpass = $DB_PASS;
        //$this->connect();
        //$this->dbh->query('SET NAMES ' . 'UTF8');

        try {

            if ($this->ATTR_PERSISTENT) { //长连接
                $this->dbh = new PDO($this->dsn, $DB_USER, $DB_PASS, array(PDO::ATTR_PERSISTENT => true));
            } else {
                $this->dbh = new PDO($this->dsn, $DB_USER, $DB_PASS);
            }
        } catch (PDOException $e) {
            exit('连接失败:' . $e->getMessage());
        }

        $this->isconnedted = true;

        $this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        $this->dbh->query('SET NAMES UTF8');
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    //获取数据表里的字段       
    public function getFields($table) {
        $this->sth = $this->dbh->query("DESCRIBE $table");
        $this->getPDOError();
        $this->sth->setFetchMode(PDO::FETCH_ASSOC);
        $result = $this->sth->fetchAll();
        $this->sth = null;

        $this->sqlquerynum++;

        return $result;
    }

    //获取要操作的数据       
    private function getCode($table, $args) {
        $allTables = require_once(DOCUMENT_ROOT . '/cache/tables.php');
        if (!is_array($allTables[$table])) {
            exit('表名错误或未更新缓存!');
        }
        $tables = array_flip($allTables[$table]);
        $unarr = array_diff_key($args, $tables);
        if (is_array($unarr)) {
            foreach ($unarr as $k => $v) {
                unset($args[$k]);
            }
        }
        $code = '';
        if (is_array($args)) {
            foreach ($args as $k => $v) {
                if ($v == '') {
                    continue;
                }
                $code .= "`$k`='$v',";
            }
        }
        $code = substr($code, 0, -1);
        return $code;
    }

    /* 插入数据 */

    public function insert($table, $arr, $debug = null) {
        if (!$this->isconnedted)
            $this->connect();
        $i = 0;
        $a = array();
        $b = array();
        $c = array();
        foreach ($arr as $k => $v) {
            $a[$i] = '`' . $k . '`';
            $b[$i] = '?';
            $c[$i] = $v;
            $i++;
        }
        $sql = 'insert into `' . $table . '`  (' . implode(', ', $a) . ') values (' . implode(', ', $b) . ')';

        $stmt = $this->dbh->prepare($sql);

        $this->getPDOError();

        $mycount = $i;

        for ($i = 0; $i < $mycount; $i++) {

            $stmt->bindParam(($i + 1), $c[$i]);
        }

        $stmt->execute();
        if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('INSERT',$sql,$arr,__FILE__,__LINE__);         
        }//记录插入日志

        $this->getPDOError();

        $this->sqlquerynum++;

        return $this->dbh->lastinsertid();
        //$this->getPDOError();
    }

    //查询数据       
    public function fetch($table, $condition = '', $sort = '', $limit = '', $field = '*', $debug = false) {
        $sql = "SELECT {$field} FROM `{$table}`";
        if (false !== ($con = $this->getCondition($condition))) {
            $sql .= $con;
        }
        if ($sort != '') {
            $sql .= " ORDER BY $sort";
        }
        if ($limit != '') {
            $sql .= " LIMIT $limit";
        }
        if ($debug)
            echo $sql;
        $this->sth = $this->dbh->query($sql);
        $this->getPDOError();
        $this->sth->setFetchMode(PDO::FETCH_ASSOC);
        $result = $this->sth->fetchAll();
        $this->sth = null;
        if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('FETCH',$sql,$condition,__FILE__,__LINE__);         
        }//记录查询日志

        $this->sqlquerynum++;

        return $result;
    }

    //查询数据       
    public function fetchOne($sql, $para = null, $debug = false) {
        if (!$this->isconnedted)
            $this->connect();

        $stmt = $this->dbh->prepare($sql);

        $this->getPDOError();

        if ($para == null) {
            $stmt->execute();
        } else {
            $stmt->execute($para);
        }

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $this->sqlquerynum++;
        if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('FETCHONE',$sql,$para,__FILE__,__LINE__);         
        }//记录查询日志        

        return $stmt->fetch();
    }

    /*获取记录总数       
     * 返回记录数
    */
    public function counts($sql, $para = null, $debug = false) {
        if (!$this->isconnedted)
            $this->connect();

        $stmt = $this->dbh->prepare($sql);

        if ($para == null) {
            $stmt->execute();
        } else {
            $stmt->execute($para);
        }

        // $count = $this->dbh->query($sql);
        $this->getPDOError();

        $this->sqlquerynum++;

        return $stmt->fetchColumn();
    }

    /* 执行sql,返回受影响记录数       */

    public function doSql($sql, $para = null, $debug = false) {
        if ($debug)
            echo $sql;

        if (!$this->isconnedted)
            $this->connect();

        $stmt = $this->dbh->prepare($sql);

        $this->getPDOError();

        if ($para == null) {
            $stmt->execute();
        } else {
            $stmt->execute($para);
        }

        $this->sqlquerynum++;
        
         if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('DOSQL',$sql,$para,__FILE__,__LINE__);         
        }//记录日志        

        return $stmt->rowCount();
    }

    //修改数据       
    public function update($table, $rs, $where, $para = null, $debug = false) {
        if (!$this->isconnedted)
            $this->connect();

        $sql = 'update `' . $table . '` set ';

        $a = Array();
        $b = Array();


        /* 生成sql字符串 */
        foreach ($rs as $k => $v) {
            $a[] = '`' . $k . '`=:' . $k;

            $b[':' . $k] = $v;
        }

        if (null != $para) {
            foreach ($para as $k => $v) {
                $b[$k] = $v;
            }
        }

        $sql .= join(',', $a) . ' where ' . $where;

        $stmt = $this->dbh->prepare($sql);

        $stmt->execute($b);

        $this->sqlquerynum++;
        
         if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('UPDATE',$sql,$para,__FILE__,__LINE__);         
        }//记录日志 
        return $stmt->rowCount();
    }

    //字段递增       
    public function increase($table, $condition, $field, $debug = false) {
        $sql = "UPDATE `$table` SET $field = $field + 1";
        if (false !== ($con = $this->getCondition($condition))) {
            $sql .= $con;
        }
        if ($debug)
            echo $sql;
        
         if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('INCREASE',$sql,$condition,__FILE__,__LINE__);         
        }//记录日志 
        
        if (($rows = $this->dbh->exec($sql)) > 0) {
            $this->getPDOError();
            return $rows;
        }
        return false;
    }

    /* 删除记录       */

    public function del($sql, $para = null) {
        if (!$this->isconnedted)
            $this->connect();

        $stmt = $this->dbh->prepare($sql);

        $this->getPDOError();

        if ($para == null) {
            $stmt->execute();
        } else {
            $stmt->execute($para);
        }
         if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('DEL',$sql,$para,__FILE__,__LINE__);         
        }//记录日志         
        $this->sqlquerynum++;

        return $stmt->rowCount();
    }

    //事务处理
    public function begintrans() {
		if (!$this->isconnedted)
            $this->connect();
        //$this->dbh->setAttribute( PDO::ATTR_AUTOCOMMIT, 0);
        $this->dbh->beginTransaction();
    }

// end func

    public function submittrans() {
        $this->dbh->commit();
        //$this->dbh->setAttribute( PDO::ATTR_AUTOCOMMIT, 1);
    }

    public function rollbacktrans() {
        $this->dbh->rollback();
        //$this->dbh->setAttribute( PDO::ATTR_AUTOCOMMIT, 1);
    }

    /**
     * 执行无返回值的SQL查询     
     *     
     */
    public function exeno($sql) {
        $this->dbh->exec($sql);
        $this->getPDOError();
    }

    public function execute($sql, $para=null, $haspage=false) {   
    //$sql  = str_replace('select', 'select sql_calc_found_rows ', $sql);
    //$sql.=';SELECT FOUND_ROWS();';
    //echo $sql;
    //die;
     if (!$this->isconnedted)
            $this->connect();
    /*if sql语句左边有空格 then 去掉*/
    if ( false !== stripos($sql, ' ') ){
        $sql = trim( $sql );
    }

    /*带翻页时加上返回总记录数*/
    if ($haspage !== false) {

        /*把select 加上 sql_calc_found_rows */ 
        $sql = substr($sql, stripos($sql, 'select')+6);

        $sql = 'select sql_calc_found_rows '.$sql;
    }
    
    $stmt = $this->dbh->prepare($sql);

    $this->getPDOError();



    if ($para == null) {
        $stmt->execute();
    }
    else {
        $stmt->execute($para);
    }   
    /*选择*/
    if (stripos($sql, 'select')===0) {
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $rs = $stmt->fetchAll();

        $a['rs'] = $rs;
        $a['total'] = $this->foundRows();

        return $a;  
    }
    elseif (stripos($sql, 'delete')===0) {
        return $stmt->rowCount();   
    }

    //$this->dbh->exec($sql);   
    //$this->getPDOError();   
}   


    public function fetchAll($sql, $para = null, $haspage = false) {
        if (!$this->isconnedted)
            $this->connect();



        /* if sql语句左边有空格 then 去掉 */
        if (false !== stripos($sql, ' ')) {
            $sql = trim($sql);
        }

        /* 带翻页时加上返回总记录数 */
        if ($haspage) {
            /* 把select 加上 sql_calc_found_rows */
            $sql = substr($sql, stripos($sql, 'select') + 6);
            $sql = 'select sql_calc_found_rows ' . $sql;
        }

        $stmt = $this->dbh->prepare($sql);

        $this->getPDOError();

        if ($para == null) {
            $stmt->execute();
        } else {
            $stmt->execute($para);
        }

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $this->sqlquerynum++;
         if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){
            $GLOBALS['log']->autosql('FETCHALL',$sql,$para,__FILE__,__LINE__);         
        }//记录日志         
        if ($haspage) {
            $rs = $stmt->fetchAll();

            $a['rs'] = $rs;
            $a['total'] = $this->foundRows();

            return $a;
        } else {
            return $stmt->fetchAll();
        }
    }

    public function foundRows() {
        $rows = $this->dbh->prepare('SELECT found_rows() AS rows');
        $rows->execute();
        $rowsCount = $rows->fetch(PDO::FETCH_OBJ)->rows;
        $rows->closeCursor();
        return $rowsCount;
    }

    /**
     * 捕获PDO错误信息     
     */
    private function getPDOError() {
        if ($this->dbh->errorCode() != '00000') {

            $error = $this->dbh->errorInfo();
            exit($error[2]);
        }
    }

    //字段关联数组处理
//	public function FDFields($data, $link = ',', $judge = array(), $aliasTable = ''){
//		$sql = '';
//		$mapData = array();
//		foreach($data as $key => $value) {
//			$mapIndex = ':' . ($link != ',' ? 'c' : '') . $aliasTable . $key;
//			$sql .= ' ' . ($aliasTable ? $aliasTable . '.' : '') . '`' . $key . '` ' . ($judge[$key] ? $judge[$key] : '=') . ' ' . $mapIndex . ' ' . $link;
//			$mapData[$mapIndex] = $value;
//		}
//		$sql = trim($sql, $link);
//		return array($sql, $mapData);
//	}
    //关闭数据连接       
    public function __destruct() {
        $this->dbh = null;
    }

}
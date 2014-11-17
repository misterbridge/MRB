<?php

abstract class Model    {

    protected $pkname;
    protected $tablename;
    protected $dbhfnname;
    protected $QUOTE_STYLE; // valid types are MYSQL,MSSQL,ANSI
    protected $COMPRESS_ARRAY;
    public $rs = array(); // for holding all object property variables

    function __construct($tablename = '', $pkname = 'id', $dbhfnname = 'getdbh', $quote_style = 'MYSQL', $compress_array = true) {
        $this->tablename      = $tablename; //Corresponding table in database
        $this->pkname         = $pkname; //Name of auto-incremented Primary Key
        $this->dbhfnname      = $dbhfnname; //dbh function name
        $this->QUOTE_STYLE    = $quote_style;
        $this->COMPRESS_ARRAY = $compress_array;
    }

    /**
     * Get a specific value of the object
     * @param  mixed $key key of the wanted value
     * @return mixed      Corresponding value
     */
    protected function get($key) {
        return $this->rs[$key];
    }

    /**
     * Set a value to a specific key of the object
     * @param mixed $key Key of the value
     * @param mixed $val Value to be set
     */
    protected function set($key, $val) {
        if (isset($this->rs[$key])) {
            $this->rs[$key] = $val;
        }
        return $this;
    }

    public function __get($key) {
        return $this->get($key);
    }

    public function __set($key, $val) {
        return $this->set($key, $val);
    }

    /**
     * Get the DBH
     * @return PDO The PDO DBH
     */
    protected function getdbh() {
        // return call_user_func($this->dbhfnname);
        if (!isset($GLOBALS['dbh'])) {
            try {
                //$GLOBALS['dbh'] = new PDO('sqlite:'.APP_PATH.'db/dbname.sqlite');
                $dbh = Registry::getInstance()->getConf('db');
                $GLOBALS['dbh'] = new PDO($dbh['base'].':host='.$dbh['host'].';dbname='.$dbh['name'], $dbh['user'], $dbh['password']);
            } catch (PDOException $e) {
                die('Connection failed: '.$e->getMessage());
            }
        }
        return $GLOBALS['dbh'];
    }

    /**
     * Equote a value
     *  -> Adapt to the DB used
     * @param  string $name Value to be enquoted
     * @return string       Enquoted value
     */
    protected function enquote($name) {
        if ($this->QUOTE_STYLE=='MYSQL') {
            return '`'.$name.'`';
        } elseif ($this->QUOTE_STYLE=='MSSQL') {
            return '['.$name.']';
        } else {
            return '"'.$name.'"';
        }
    }
    
    /////////////////////
    // Basic functions //
    /////////////////////
    /**
     * Return true if the primary key is set
     *  -> The actual entry exists in database
     * @param  boolean $checkdb If set to true, if the primary key value isn't set, will check the data base for a primary key < 1
     * @return mixed            Mainly true or false, but if the checkdb is done, the number of value that have been found (basically true for >= 1)
     */
    public function exists($checkdb = false) {
        if ((int)$this->rs[$this->pkname] < 1) {
            return false;
        } else if (!$checkdb) {
            return true;
        }
        $dbh = $this->getdbh();
        $sql = 'SELECT 1 FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname)."='".$this->rs[$this->pkname]."'";
        $result = $dbh->query($sql)->fetchAll();
        return count($result);
    }

    /**
     * Retrieve a object based on it's very primary key
     * @param  int      $pkvalue The primary key
     * @return object            The related object
     */
    public function retrieve($pkvalue) {
        $dbh = $this->getdbh();
        $sql = 'SELECT * FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname).'=?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1,(int)$pkvalue);
        $stmt->execute();
        $rs = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rs) {
            foreach ($rs as $key => $val) {
                if (isset($this->rs[$key])) {
                    $this->rs[$key] = is_scalar($this->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
                }
            }
        }
        return $this;
    }
    
    /**
     * Retrieve an object base on other criteria than the PK
     * @param  string $wherewhat WHERE criterias
     * @param  string $bindings  Binding values to WHERE columns
     * @return object            The related object
     * @example
     *     $user->retrieve_one("username=?",'erickoh');
     *     $user->retrieve_one("username=? AND password=? AND status='enabled'",array('erickoh','123456'));
     */
    public function retrieve_one($wherewhat = '', $bindings = '') {
        $dbh = $this->getdbh();
        if (is_scalar($bindings)) {
            $bindings= trim($bindings) ? array($bindings) : array();
        }
        $sql = 'SELECT * FROM '.$this->enquote($this->tablename);
        if ($wherewhat) {
            $sql .= ' WHERE '.$wherewhat;
        }
        $sql .= ' LIMIT 1';
        $stmt = $dbh->prepare($sql);
        $i = 0;
        foreach($bindings as $v) {
            $stmt->bindValue(++$i,$v);
        }
        $stmt->execute();
        $rs = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$rs) {
            return false;
        }
        foreach ($rs as $key => $val) {
            if (isset($this->rs[$key])) {
                $this->rs[$key] = is_scalar($this->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
            }
        }
        return $this;
    }
    
    /**
     * Retrieve several objects from criterias
     * @param  string $wherewhat WHERE criterias
     * @param  string $bindings  Binding values to WHERE criterias
     * @return array(<Model>)    An array of related objects
     * @example
     *     $array = $user->retrieve_many("username LIKE ?",'eric%');
     */
    public function retrieve_many($wherewhat = '', $bindings = '') {
        $dbh=$this->getdbh();
        if (is_scalar($bindings)) {
            $bindings=trim($bindings) ? array($bindings) : array();
        }
        $sql = 'SELECT * FROM ' . $this->tablename;
        if ($wherewhat) {
            $sql .= ' WHERE ' . $wherewhat;
        }
        $stmt = $dbh->prepare($sql);
        $i=0;
        foreach($bindings as $v) {
            $stmt->bindValue(++$i,$v);
        }
        $stmt->execute();
        $arr = array();
        $class = get_class($this);
        while ($rs = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $myclass = new $class();
            foreach ($rs as $key => $val) {
                if (isset($myclass->rs[$key])) {
                    $myclass->rs[$key] = is_scalar($myclass->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
                }
            }
            $arr[] = $myclass;
        }
        return $arr;
    }

    /**
     * Return selected fields of particular criterias
     * @param  string $selectwhat     Coulmns wanted
     * @param  string $wherewhat      WHERE criterias
     * @param  array  $bindings       Bindings values to WHERE criterias
     * @param  const  $pdo_fetch_mode PDO::FETCH mode
     * @return array                  Result of the specific SELECT
     * @example
     *     $array = $user->select("username,email","username LIKE ?",'eric%');
     */
    public tfunction select($selectwhat = '*', $wherewhat = '', $bindings = '', $pdo_fetch_mode = PDO::FETCH_ASSOC) {
        $dbh = $this->getdbh();
        if (is_scalar($bindings)) {
            $bindings = trim($bindings) ? array($bindings) : array();
        }
        $sql = 'SELECT '.$selectwhat.' FROM '.$this->tablename;
        if ($wherewhat) {
            $sql .= ' WHERE '.$wherewhat;
        }
        $stmt = $dbh->prepare($sql);
        $i = 0;
        foreach($bindings as $v) {
            $stmt->bindValue(++$i,$v);
        }
        $stmt->execute();
        return $stmt->fetchAll($pdo_fetch_mode);
    }
    
    //////////////////////////////
    // Create / Update / Delete //
    //////////////////////////////
    /**
     * Create or Update the object
     *  -> Depends of the existance of this one in the database
     * @return $this Current Model object
     */
    public function save() {
        $action = $this->exists() ? 'update' : 'create';
        return $this->$action();
    }

    /**
     * Inserts record into database with a new auto-incremented primary key
     *  -> If the primary key is empty, then the PK column should have been set to auto increment
     * @return object The current model object
     */
    public function create() {
        $dbh = $this->getdbh();
        $pkname = $this->pkname;
        $s1 = $s2 = '';
        foreach ($this->rs as $k => $v) {
            if ($k != $pkname || $v) {
                $s1 .= ','.$this->enquote($k);
                $s2 .= ',?';
            }
        }
        $sql = 'INSERT INTO ' . $this->enquote($this->tablename) . ' (' . substr($s1,1) . ') VALUES (' . substr($s2,1) . ')';
        $stmt = $dbh->prepare($sql);
        $i = 0;
        foreach ($this->rs as $k => $v) {
            if ($k != $pkname || $v) {
                $stmt->bindValue(++$i,is_scalar($v) ? $v : ($this->COMPRESS_ARRAY ? gzdeflate(serialize($v)) : serialize($v)) );
            }
        }
        $stmt->execute();
        if (!$stmt->rowCount()) {
            return false;
        }
        $this->set($pkname, $dbh->lastInsertId());
        return $this;
    }

    /**
     * Update the database value with the new object
     * @return object Statement execution
     */
    public function update() {
        $dbh = $this->getdbh();
        $s = '';
        foreach ($this->rs as $k => $v) {
            $s .= ',' . $this->enquote($k) . '=?';
        }
        $s = substr($s,1);
        $sql = 'UPDATE ' . $this->enquote($this->tablename) . ' SET ' . $s . ' WHERE ' . $this->enquote($this->pkname) . '=?';
        $stmt = $dbh->prepare($sql);
        $i = 0;
        foreach ($this->rs as $k => $v) {
            $stmt->bindValue(++$i,is_scalar($v) ? $v : ($this->COMPRESS_ARRAY ? gzdeflate(serialize($v)) : serialize($v)) );
        }
        $stmt->bindValue(++$i,$this->rs[$this->pkname]);
        return $stmt->execute();
    }

    /**
     * Delete the entry from the database
     * @return object Statement execution
     */
    public function delete() {
        $dbh = $this->getdbh();
        $sql = 'DELETE FROM ' . $this->enquote($this->tablename) . ' WHERE ' . $this->enquote($this->pkname) . '=?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1,$this->rs[$this->pkname]);
        return $stmt->execute();
    }
    
    ////////////
    // Useful //
    ////////////
    /**
     * Merge an array to the current object
     * @param  array $arr Associative array
     * @return object     Current object with some new values added and / or modified
     */
    public function merge($arr) {
        if (!is_array($arr)) {
            return $this;
        }
        foreach ($arr as $key => $val) {
            if (isset($this->rs[$key])) {
               $this->rs[$key] = $val;
           }
        }
        return $this;
    }
}
<?php
class SqlStatement {
    
    public static $DB_PREFIX = '';
    public static $DB_CONNECTOR = null;
    protected $_select = array();
    protected $_distinct = false;
    protected $_from = array();
    protected $_where = array();
    protected $_having = array();
    protected $_join = array();
    protected $_order = array();
    protected $_group = array();
    protected $_limit = array('offset' => 0, 'limit' => 0);
    protected $_mode = 'select';
    protected $_insert = null;
    protected $_ignore = false;
    protected $_union = array();
    protected $_update = null;
    protected $_set = array();
    
    public function __toString() {
        $str = '';
        if ($this->_mode == 'select') {
            $str = $this->buildSelectQuery();
        } elseif ($this->_mode == 'delete') {
            $str = $this->buildDeleteQuery();
        } elseif ($this->_mode == 'insert') {
            $str = $this->buildInsertQuery();
        } elseif ($this->_mode === 'union') {
            $str = $this->buildUnionQuery();
        } elseif ($this->_mode === 'update') {
            $str = $this->buildUpdateQuery();
        }
        
        return $str;
    }
    
    /**
     * SELECT statement
     * @param array $columns Optional. Use array keys to set an alias for colun like so:
     * array('a' => 'attribute') - will be converted to a.attribute
     * @return \SqlStatement
     */
    public function select($columns = array()){
        if (count($columns)) {
            $this->_select = array_merge($this->_select, $columns);
        }
        $this->_mode = 'select';
        return $this;
    }
    /**
     * Set/Unset DISTINCT operator
     * @param boolean $d TRUE by Default
     * @return \SqlStatement
     */
    public function distinct($d = true)
    {
        $this->_distinct = $d;
        return $this;
    }
    /**
     * WHERE Statement
     * @param string $condition 
     * @param array $params Optional. Parameters which should replace question marks. 
     * @return \SqlStatement
     */
    public function where($condition, $params = array()){
        if (is_array($params)) {
            if (count($params)) {
                foreach ($params as $param) {
                    $condition = str_replace('?', "'" . self::escape($param) . "'", $condition);
                }
            }
        } else {
            $condition = str_replace('?', "'" . self::escape($params) . "'", $condition);
        }
        array_push($this->_where, $condition);
        return $this;
    }
    
    public function in($column, $data, $not = false) {
        if (!is_array($data) || !count($data)) {
            return $this;
        }
        $preparedData = array();
        foreach ($data as $item) {
            $preparedData[] = self::_quote($item);
        }
        $this->where($column . ($not ? ' NOT' : '') . ' IN (' . implode(',', $preparedData) . ')');
        return $this;
    }
    /**
     * Multiple Where conditions
     * Merges multiple conditions with specified logical operator
     * @param array $conditions array(array(<condition>[,<parameter>]), ...)
     * @param string $operator Can be OR or AND. AND operator is set by default
     */
    public function multipleWhere($conditions, $operator = 'OR')
    {
        if (count($conditions)) {
            $conArr = array();
            foreach ($conditions as $item) {
                if (is_array($item)) {
                    if (count($item) > 1) {
                        $conArr[] = str_replace('?', "'" . self::escape($item[1]) . "'", $item[0]);
                    } else {
                        $conArr[] = $item[0];
                    }
                } else {
                    $conArr[] = $item;
                }
            }
            array_push($this->_where, '(' . implode(" $operator ", $conArr) . ')');
        }
        return $this;
    }
    /**
     * FROM statement
     * @param array|string $table Use array to set an alias for the table
     * @param array $columns Optional. Array of columns to output
     * @return \SqlStatement
     */
    public function from($table, $columns = array()){
        if (is_array($table)) {
            $tblName = (is_string(current($table))) ? '`' . self::$DB_PREFIX . current($table) . '`' : current($table);
            $this->_from[key($table)] = $tblName;
        } else {
            array_push($this->_from, '`' . self::$DB_PREFIX . $table . '`');
        }
        if (!empty($columns)) {
            $this->select($columns);
        }
        
        return $this;
    }
    /**
     * JOIN statement
     * @param string|array $table Table name
     * @param string $type INNER|LEFT|RIGHT
     * @param strin $on ON condition
     * @param array $columns Optional
     * @return \SqlStatement
     */
    public function join($table, $type, $on, $columns = array()){
        if (is_array($table)) {
            $tblName = (is_string(current($table))) ? '`' . self::$DB_PREFIX . current($table) . '`' : current($table);
            $this->_join[key($table)] = array('table' => $tblName, 'type' => $type, 'on' => $on);
        } else {
            $tblName = (is_string($table)) ? '`' . self::$DB_PREFIX . $table . '`' : $table;
            array_push($this->_join, array('table' => $tblName, 'type' => $type, 'on' => $on));
        }
        $this->_select = array_merge($this->_select, $columns);
        return $this;
    }
    /**
     * INNER JOIN statement
     * @param string|array $table Table name
     * @param strin $on ON condition
     * @param array $columns Optional
     * @return \SqlStatement
     */
    public function innerJoin($table, $on, $columns = array()){
        return $this->join($table, 'INNER', $on, $columns);
    }
    /**
     * LEFT JOIN statement
     * @param string|array $table Table name
     * @param strin $on ON condition
     * @param array $columns Optional
     * @return \SqlStatement
     */
    public function leftJoin($table, $on, $columns = array()){
        return $this->join($table, 'LEFT', $on, $columns);
    }
    /**
     * RIGHT JOIN statement
     * @param string|array $table Table name
     * @param strin $on ON condition
     * @param array $columns Optional
     * @return \SqlStatement
     */
    public function rightJoin($table, $on, $columns = array()){
        return $this->join($table, 'RIGHT', $on, $columns);
    }
    /**
     * GROUP BY statement
     * @param array $columns Columns to group by. E.g. array('a.id', 'a.name')
     * @return \SqlStatement
     */
    public function group($columns){
        $this->_group = array_merge($this->_group, $columns);
        return $this;
    }
    /**
     * OREDER BY statement
     * @param array $columns Columns to order by. E.g. array('a.id ASC', 'a.name DESC')
     * @return \SqlStatement
     */
    public function order($columns){
        $this->_order = array_merge($this->_order, $columns);
        return $this;
    }
    /**
     * LIMIT statement
     * @param int $limit Limit
     * @param int $offset Optional. 0 by default
     * @return \SqlStatement
     */
    public function limit($limit, $offset = 0) {
        $this->_limit = array('offset' => $offset, 'limit' => $limit);
        return $this;
    }
    /**
     * HAVING statement
     * @param string $condition Condition. Can be parameterized with question marks
     * @param array $params Optional.
     * @return \SqlStatement
     */
    public function having($condition, $params = array()) {
        if (is_array($params)) {
            if (count($params)) {
                foreach ($params as $param) {
                    $condition = str_replace('?', "'" . self::escape($param) . "'", $condition);
                }
            }
        } else {
            $condition = str_replace('?', "'" . self::escape($params) . "'", $condition);
        }
        array_push($this->_having, $condition);
        return $this;
    }
    /**
     * Build Select query string
     * @return string SQL string
     */
    protected function buildSelectQuery() {
        $sql = ' SELECT ' . ($this->_distinct ? 'DISTINCT ' : '');
        $arr = array();
        if (count($this->_select)) {
            foreach ($this->_select as $alias => $col) {
                $col = ($col instanceof SqlStatement) ? "($col)" : $col;
                $arr[] = (is_numeric($alias)) ? $col : $col . ' AS ' . $alias;
            }
            $sql .= implode(', ', $arr);
        } else {
            $sql .= ' * ';
        }
        
        $sql .= ' FROM ';
        $arr = array();
        foreach ($this->_from as $alias => $tbl) {
            if ($tbl instanceof SqlStatement) {
                $tbl = '(' . $tbl . ')';
            }
            $arr[] = (!is_numeric($alias)) ? $tbl . ' AS ' . $alias : $tbl;
        }
        $sql .= implode(', ', $arr);
        
        if (count($this->_join)) {
            foreach ($this->_join as $alias => $join) {
                $sql .= ' ' . $join['type'] . ' JOIN ';
                if ($join['table'] instanceof SqlStatement) {
                    $sql .= '(' . $join['table'] . ')';
                } else {
                    $sql .= $join['table'];
                }
                if (!is_numeric($alias)) {
                    $sql .= ' AS ' . $alias;
                }
                $sql .= ' ON (' . $join['on'] . ')';
            }
        }
        
        if (count($this->_where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->_where);
        }
        if (count($this->_group)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->_group);
        }
        if (count($this->_having)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->_having);
        }
        if (count($this->_order)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->_order);
        }
        if ($this->_limit['limit'] > 0) {
            $sql .= ' LIMIT ' . $this->_limit['offset'] . ', ' . $this->_limit['limit'];
        }
        
        return $sql;
    }
    /**
     * Partially or fully resets SqlStatement object
     * @param string $part Optional. SQL part. E.g. select or where, or from, etc.
     * @return \SqlStatement
     */
    public function clean($part = null) {
        if ($part) {
            switch ($part) {
                case 'limit' :
                    $this->limit(0, 0);
                    break;
                case 'distinct' :
                    $this->distinct(false);
                    break;
                case 'mode' :
                    $this->_mode = 'select';
                    break;
                case 'ignore' :
                    $this->_ignore = false;
                    break;
                default:
                    if (isset($this->{'_' . $part})) {
                        $this->{'_' . $part} = array();
                    }
                    break;
            }
        } else {
            $this->_select = array();
            $this->_distinct = false;
            $this->_from = array();
            $this->_where = array();
            $this->_join = array();
            $this->_order = array();
            $this->_group = array();
            $this->_limit = array('offset' => 0, 'limit' => 0);
            $this->_mode = 'select';
            $this->_insert = null;
            $this->_ignore = false;
            $this->_union = array();
            $this->_update = null;
            $this->_set = array();
        }
        return $this;
    }
    /**
     * Build Delete query string
     * @return string SQL string
     */
    public function buildDeleteQuery()
    {
        $sql = 'DELETE FROM ' . reset($this->_from);
        
        if (count($this->_where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->_where);
        }
        
        return $sql;
    }
    /**
     * DELETE statement
     * @return \SqlStatement
     */
    public function delete() {
        $this->_mode = 'delete';
        return $this;
    }
    /**
     * INSERT INTO statement
     * @param string $tbl target table name
     * @param array $values 
     * @param array|NULL $columns Optional. Predefined list of columns. If it is not set, the keys from $values item will be used as columns
     * @return \SqlStatement
     */
    public function insertInto($tbl, $values, $columns = null) {
        $this->_insert = array(
            'into' => '`' . self::$DB_PREFIX . $tbl . '`',
            'columns' => $columns,
            'values' => $values
            );
        $this->_mode = 'insert';
        
        return $this;
    }
    /**
     * Set/Unset IGNORE modifier
     * @param boolean $ignore Optional. TRUE by default
     * @return \SqlStatement
     */
    public function ignore($ignore = true) {
        $this->_ignore = $ignore;
        return $this;
    }
    /**
     * Build Insert query string
     * @return string SQL string
     */
    public function buildInsertQuery()
    {
        $sql = 'INSERT' . (($this->_ignore) ? ' IGNORE' : '') 
             . ' INTO ' . $this->_insert['into'];

        if (!empty($this->_set)) {
            $sql .= implode(' , ', $this->_set);
        } else {
            $columns = (is_array($this->_insert['columns'])) 
                    ? $this->_insert['columns']
                    : array_keys($this->_insert['values'][0]);

            $sql .= ' (' . implode(',', $columns) . ')';

            if ($this->_insert['values'] instanceof SqlStatement || is_string($this->_insert['values'])) {
                $sql .= ' ' . $this->_insert['values'];
            } else {
                $arr = array();
                $colNum = count($columns);
                foreach ($this->_insert['values'] as $vals) {
                    if (is_array($vals)) {
                        $vals  = array_splice($vals, 0, $colNum); 
                    }
                    $arr[] = '(' . implode(',', array_map(array(__CLASS__, '_quote'), $vals)) . ')';
                }
                $sql .= ' VALUES ' . implode(',', $arr);
            }
        }
        
        return $sql;
    }
    
    public function union($subQueries = array())
    {
        $this->_mode = 'union';
        if (is_array($subQueries)) {
            $this->_union = $subQueries;
        } elseif (!empty($subQueries)) {
            $this->_union = array($subQueries);
        }
        
        return $this;
    }
    
    public function buildUnionQuery()
    {
        $sql = '';
        if (count($this->_union)) {
            $sql = implode(' UNION ', $this->_union);
        }
        
        return $sql;
    }
    
    public function match($target, $query, $putInSelect = false, $mode = 'IN BOOLEAN MODE')
    {
        $query = self::escape($query);
        
        if (is_array($target)) {
            $alias = key($target);
            $fields = array_shift($target);
            $matchStatement = "MATCH({$fields}) AGAINST ('{$query}' {$mode})";
            if ($putInSelect) {
                if (is_string($alias)) {
                    $this->select(array($alias => $matchStatement));
                } else {
                    $this->select(array($matchStatement));
                }
            }
            $this->where($matchStatement);
        } else {
            $matchStatement = "MATCH({$target}) AGAINST ('{$query}' {$mode})";
            if ($putInSelect) {
                $this->select(array($matchStatement));
            }
            $this->where($matchStatement);
        }
        
        return $this;
    }
    
    public function update($table)
    {
        $this->_mode = 'update';
        if (is_array($table)) {
            $tblName = (is_string(current($table))) ? '`' . self::$DB_PREFIX . current($table) . '`' : current($table);
            $this->_update = array(key($table) => $tblName);
        } else {
            $this->_update = '`' . self::$DB_PREFIX . $table . '`';
        }
        
        return $this;
    }
    
    public function set($set){
        if (!is_array($set)) {
            $set = array($set);
        }
        foreach ($set as $setItem) {
            if (!is_array($setItem)) {
                $setItem = array($setItem);
            }
            $params = isset($setItem[1]) ? $setItem[1] : array();
            $params = is_array($params) ? $params : array($params);
            $condition = $setItem[0];
            
            if (count($params)) {
                foreach ($params as $param) {
                    $condition = str_replace('?', "'" . self::escape($param) . "'", $condition);
                }
            }
            
            array_push($this->_set, $condition);
        }
        return $this;
    }
    
    
    /**
     * Build Update query string
     * @return string SQL string
     */
    public function buildUpdateQuery()
    {
        if (is_array($this->_update)) {
            $name  = reset($this->_update);
            $alias = key($this->_update);
            $tbl = (!is_numeric($alias)) ? $name . ' AS ' . $alias : $name;
        }
        $sql = 'UPDATE ' . $tbl;
        
        if (count($this->_join)) {
            foreach ($this->_join as $alias => $join) {
                $sql .= ' ' . $join['type'] . ' JOIN ';
                if ($join['table'] instanceof SqlStatement) {
                    $sql .= '(' . $join['table'] . ')';
                } else {
                    $sql .= $join['table'];
                }
                if (!is_numeric($alias)) {
                    $sql .= ' AS ' . $alias;
                }
                $sql .= ' ON (' . $join['on'] . ')';
            }
        }
        
        if (count($this->_set)) {
            $sql .= ' SET ' . implode(', ', $this->_set);
        }
        
        if (count($this->_where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->_where);
        }

        
        return $sql;
    }
    
    public static function escape($param)
    {
        if (is_null(self::$DB_CONNECTOR)) {
            throw new Exception('DB connector was not set up. Set self::$DB_CONNECTOR by current database connection object');
        }
        return self::$DB_CONNECTOR->escape($param);
        
    }
    
    protected static function _quote($i)
    {
        return "'" . SqlStatement::escape($i) . "'";
    }
}
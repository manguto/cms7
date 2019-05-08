<?php
namespace manguto\cms5\lib\database\mysql\mysqli;

use manguto\cms5\lib\Exception;

class Mysqli
{

    protected $connection;

    protected $query;

    public $query_count = 0;

    public function __construct($dbhost = '', $dbuser = '', $dbpass = '', $dbname = '', $charset = '')
    {
        if ($dbhost == '' && $dbuser == '' && $dbpass == '' && $dbname == '' && $charset == '') {
            $dbhost = DATABASE_HOST;
            $dbuser = DATABASE_USER;
            $dbpass = DATABASE_PASS;
            $dbname = DATABASE_NAME;
            $charset = DATABASE_CHARTSET;
        }
        
        $this->connection = new \mysqli($dbhost, $dbuser, $dbpass, $dbname);
        if ($this->connection->connect_error) {
            die('Failed to connect to MySQL - ' . $this->connection->connect_error);
        }
        $this->connection->set_charset($charset);
    }

    // -------------------------------------------------------------------------------------------------------------------
    public function query($query)
    {
        if ($this->query = $this->connection->prepare($query)) {
            if (func_num_args() > 1) {
                $x = func_get_args();
                $args = array_slice($x, 1);
                $types = '';
                $args_ref = array();
                foreach ($args as $k => &$arg) {
                    if (is_array($args[$k])) {
                        foreach ($args[$k] as $j => &$a) {
                            $types .= $this->_gettype($args[$k][$j]);
                            $args_ref[] = &$a;
                        }
                    } else {
                        $types .= $this->_gettype($args[$k]);
                        $args_ref[] = &$arg;
                    }
                }
                array_unshift($args_ref, $types);
                call_user_func_array(array(
                    $this->query,
                    'bind_param'
                ), $args_ref);
            }
            $this->query->execute();
            if ($this->query->errno) {
                throw new Exception('Unable to process MySQL query (check your params) - ' . $this->query->error);
            }
            $this->query_count ++;
        } else {
            throw new Exception('Unable to prepare statement (check your syntax) - ' . $this->query->error);
        }
        return $this;
    }

    // -------------------------------------------------------------------------------------------------------------------
    public function getInsertedId()
    {
        return $this->query->insert_id;
    }

    // -------------------------------------------------------------------------------------------------------------------
    public function fetchAll()
    {
        $params = array();
        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array(
            $this->query,
            'bind_result'
        ), $params);
        $result = array();
        while ($this->query->fetch()) {
            $r = array();
            foreach ($row as $key => $val) {
                $r[$key] = $val;
            }
            $result[] = $r;
        }
        $this->query->close();
        return $result;
    }

    // -------------------------------------------------------------------------------------------------------------------
    public function fetchArray()
    {
        $params = array();
        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array(
            $this->query,
            'bind_result'
        ), $params);
        $result = array();
        while ($this->query->fetch()) {
            foreach ($row as $key => $val) {
                $result[$key] = $val;
            }
        }
        $this->query->close();
        return $result;
    }

    // -------------------------------------------------------------------------------------------------------------------
    public function numRows()
    {
        $this->query->store_result();
        return $this->query->num_rows;
    }

    // -------------------------------------------------------------------------------------------------------------------
    public function close()
    {
        return $this->connection->close();
    }

    // -------------------------------------------------------------------------------------------------------------------
    public function affectedRows()
    {
        return $this->query->affected_rows;
    }

    // -------------------------------------------------------------------------------------------------------------------
    private function _gettype($var)
    {
        if (is_string($var))
            return 's';
        if (is_float($var))
            return 'd';
        if (is_int($var))
            return 'i';
        return 'b';
    }
}

?>
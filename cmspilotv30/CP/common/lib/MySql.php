<?
/**
 *
 */
class CP_Common_Lib_MySql
{
    /**
     *
     * @var <type>
     */
    var $db_connect_id;
    var $query_result;
    var $row = array();
    var $rowset = array();
    var $num_queries = 0;
    var $overallQueryTime = 0;

    /**
     *
     * @param <type> $sqlserver
     * @param <type> $sqluser
     * @param <type> $sqlpassword
     * @param <type> $database
     * @param <type> $persistency
     * @return <type>
     */
    function __construct($params) {
        $this->server      = $params[0];
        $this->user        = $params[1];
        $this->password    = $params[2];
        $this->dbname      = $params[3];
        $this->persistency = $params[4];

        if($this->persistency) {
            $this->db_connect_id = @mysql_pconnect($this->server, $this->user, $this->password);
        } else {
            $this->db_connect_id = mysql_connect($this->server, $this->user, $this->password);
        }

        if($this->db_connect_id) {
            $dbselect = @mysql_select_db($this->dbname);
            if(!$dbselect) {
               @mysql_close($this->db_connect_id);
               $this->db_connect_id = $dbselect;
            }

            return $this->db_connect_id;
        } else {
            return false;
        }
    }

    /**
     *
     * @return <type>
     */
    function sql_close() {
        if($this->db_connect_id) {
            if($this->query_result) {
               @mysql_free_result($this->query_result);
            }
            $result = @mysql_close($this->db_connect_id);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $query
     * @param <type> $transaction
     * @return <type>
     */
    function sql_query($query = "", $transaction = FALSE) {

        $start = (float) array_sum(explode(' ',microtime()));
        unset($this->query_result);
        $cpUtil = Zend_Registry::get('cpUtil');

        if($query != "") {
            $this->num_queries++;
            // $this->query_result = mysql_query($query, $this->db_connect_id);
            $this->query_result = mysql_query($query, $this->db_connect_id) or $this->sql_query_error_handler($query);
        }

        $end = (float) array_sum(explode(' ',microtime()));
        $secs = $end-$start;

        if($secs > 0.001){
            //FB::log("<hr>{$query}<br>Processing time: ". sprintf("%.4f", ($end-$start))." seconds");
            //print "<hr>{$query}<br>Processing time: ". sprintf("%.4f", ($end-$start))." seconds";
        }
        //FB::log("<hr>{$query}<br>Processing time: ". sprintf("%.4f", ($end-$start))." seconds");
        //print "<hr>{$query}<br>Processing time: ". sprintf("%.4f", ($end-$start))." seconds";

        $this->overallQueryTime += $end-$start;

        if($this->query_result) {
            //trace();
            unset($this->row[$this->query_result]);
            unset($this->rowset[$this->query_result]);
            return $this->query_result;
        } else {
            // print $query . "<hr>";
            // print mysql_error();
            // return false;
            //return ($transaction == END_TRANSACTION) ? true : false;
        }
    }

    /**
     *
     * @param <type> $query
     */
    function sql_query_error_handler($query) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        if ($cpCfg[CP_ENV]['display_errors']) {
            print trace();
            print "<hr>" . mysql_error();
            print "<pre class='sql'><b>Actual Query</b><hr>" . $query . '</pre>';
        } else {
            print "
            <hr>
            Some database errors have occurred.<br /><br />
            Please contact the administrator..
            <hr>
            ";
        }
    }

    /**
     *
     * @param <type> $query_id
     * @return <type>
     */
    function sql_numrows($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            $result = @mysql_num_rows($query_id);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @return <type>
     */
    function sql_affectedrows() {
        if($this->db_connect_id) {
            $result = @mysql_affected_rows($this->db_connect_id);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $query_id
     * @return <type>
     */
    function sql_numfields($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if($query_id) {
            $result = @mysql_num_fields($query_id);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $offset
     * @param <type> $query_id
     * @return <type>
     */
    function sql_fieldname($offset, $query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if($query_id) {
            $result = @mysql_field_name($query_id, $offset);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $offset
     * @param <type> $query_id
     * @return <type>
     */
    function sql_fieldtype($offset, $query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if($query_id) {
            $result = @mysql_field_type($query_id, $offset);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $query_id
     * @param <type> $fetchType
     * @return <type>
     */
    function sql_fetchrow($query_id = 0, $fetchType = MYSQL_BOTH) { // MYSQL_ASSOC
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if($query_id) {
            //$this->row[$query_id] = @mysql_fetch_array($query_id, $fetchType);
            return @mysql_fetch_array($query_id, $fetchType);
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $query_id
     * @return <type>
     */
    function sql_fetchrowset($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if($query_id) {
            unset($this->rowset[$query_id]);
            unset($this->row[$query_id]);
            while($this->rowset[$query_id] = @mysql_fetch_array($query_id)) {
                $result[] = $this->rowset[$query_id];
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $query_id
     * @return <type>
     */
    function sql_fetch_object($query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }
        if($query_id) {
            $result = @mysql_fetch_object($query_id);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $field
     * @param <type> $rownum
     * @param <type> $query_id
     * @return <type>
     */
    function sql_fetchfield($field, $rownum = -1, $query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if($query_id) {
            if($rownum > -1) {
               $result = @mysql_result($query_id, $rownum, $field);
            } else {
                if(empty($this->row[$query_id]) && empty($this->rowset[$query_id])) {
                    if($this->sql_fetchrow()) {
                        $result = $this->row[$query_id][$field];
                    }
                } else {
                    if($this->rowset[$query_id]) {
                        $result = $this->rowset[$query_id][$field];
                    } else if($this->row[$query_id]) {
                        $result = $this->row[$query_id][$field];
                    }
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $rownum
     * @param <type> $query_id
     * @return <type>
     */
    function sql_rowseek($rownum, $query_id = 0) {
        if(!$query_id) {
            $query_id = $this->query_result;
        }

        if($query_id) {
            $result = @mysql_data_seek($query_id, $rownum);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @return <type>
     */
    function sql_nextid() {
        if($this->db_connect_id) {
            $result = @mysql_insert_id($this->db_connect_id);
            return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $query_id
     * @return <type>
     */
    function sql_freeresult($query_id = 0) {
        if(!$query_id) {
           $query_id = $this->query_result;
        }

        if ($query_id) {
            unset($this->row[$query_id]);
            unset($this->rowset[$query_id]);
            @mysql_free_result($query_id);
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $query_id
     * @return <type>
     */
    function sql_error($query_id = 0) {
        $result["message"] = @mysql_error($this->db_connect_id);
        $result["code"] = @mysql_errno($this->db_connect_id);
        return $result;
    }
}

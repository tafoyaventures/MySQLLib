<?php
/**
 * Tafoya Ventures MySQL Support Library
 *
 * @copyright 2001 - 2019, Brian Tafoya.
 * @package   MySQLLib
 * @author    Brian Tafoya <btafoya@tafoyaventures.com>
 * @version   1.0
 * @license   MIT
 * @license   https://opensource.org/licenses/MIT The MIT License
 * @category  MySQL_Support_Library
 * @link      https://tafoyaventures.com Tafoya Ventures
 *
 * Copyright (c) 2019, Brian Tafoya
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


defined('OBJECT') or define('OBJECT', 'OBJECT');
defined('ARRAY_A') or define('ARRAY_A', 'ARRAY_A');
defined('ARRAY_N') or define('ARRAY_N', 'ARRAY_N');

use voku\helper\AntiXSS;

/**
 * Class MySQLLib
 */
class MySQLLib
{


    /**
     * @var mysqli $datab Object.
     */
    public $datab = false;

    /**
     * @var array $queries Queries storage array.
     */
    public $queries = array();

    /**
     * @var array $results Results storage array.
     */
    public $results = array();

    /**
     * @var array $captured_errors Errors storage array.
     */
    public $captured_errors = array();

    /**
     * @var array $col_info Col storage array.
     */
    public $col_info = array();

    /**
     * @var bool $debug Enable/disable debugging.
     */
    public $debug = true;

    /**
     * @var int $debugCnt Error counter.
     */
    public $debugCnt = 0;

    /**
     * @var null $insert_id Last insert ID.
     */
    public $insert_id = null;

    /**
     * @var bool $show_errors Show/Log errors.
     */
    public $show_errors = false;

    /**
     * @var bool $use_disk_cache Depreciated.
     */
    public $use_disk_cache = false;

    /**
     * @var bool $cache_queries Depreciated.
     */
    public $cache_queries = false;

    /**
     * @var null $result Depreciated.
     */
    public $result = null;

    /**
     * @var null $rows_affected Depreciated.
     */
    public $rows_affected = null;

    /**
     * @var null $last_query last run query.
     */
    public $last_query = null;

    /**
     * @var null $last_error last run error.
     */
    public $last_error = null;

    /**
     * @var null $last_method last run method/function.
     */
    public $last_method = null;

    /**
     * @var null $last_result last result.
     */
    public $last_result = null;

    /**
     * @var array $error_callback_method Integration for debugging/logging libraries such as phpConsole.
     */
    public $error_callback_method = array();

    /**
     * @var string $response_display_format Response format (firephp, text, json).
     */
    public $response_display_format = "firephp";

    /**
     * Title
     * @var string
     */
    protected static $title = 'MySQLi logger';

    /**
     * Query table cell HTML attributes
     * @var string
     */
    protected static $query_attributes = '';

    /**
     * Logged queries.
     * @var array
     */
    protected static $log = [];


    /**
     * MySQLLib constructor.
     * @param $database_username
     * @param $database_password
     * @param $database_name
     * @param $database_server_hostname
     * @throws Exception
     */
    public function __construct($database_username, $database_password, $database_name, $database_server_hostname)
    {
        try {
            $this->datab = new mysqli($database_server_hostname, $database_username, $database_password, $database_name);

            if ($this->datab->connect_error) {
                $this->register_error(
                    (int)$this->debugCnt, array(
                        "function" => "get_row",
                        "method" => "prepare",
                        "errno" => $this->datab->connect_errno,
                        "error" => $this->datab->connect_error
                    )
                );

                throw new Exception("Connection failure: " . $this->datab->connect_error);
            }
        } catch (Exception $e) {
            throw new Exception("Database login failed: " . $e->getMessage(), 911);
        }
    }//end __construct()


    /**
     * autocommit
     *
     * @method void autocommit() Turns on or off auto-committing database modifications
     *
     * @param bool $mode
     */
    public function autocommit($mode = true)
    {
        $this->datab->autocommit($mode);
    }//end autocommit()


    /**
     * begin
     *
     * @method void begin() Turns on transaction
     */
    public function begin()
    {
        $this->datab->begin_transaction();
    }//end begin()


    /**
     * commit
     *
     * @method void commit() Turns on transaction
     *
     * @return bool
     */
    public function commit()
    {
        return $this->datab->commit();
    }//end commit()


    /**
     * @return false|string|null
     */
    function debug()
    {
        switch ($this->response_display_format) {
            default:
                echo "<h4>" . self::$title . "</h4><br>\n";
                echo "<b>Last Query</b> " . ($this->last_query ? $this->last_query : "NULL") . "<br>\n";
                echo "<b>Last Function Call:</b> " . $this->last_method . "<br>\n";
                echo "<b>Last Error:</b> " . $this->last_error . "<br>\n";
                echo "<b>Last Results:</b> " . $this->last_result . "<br>\n";

                return null;
                break;
            case "text":
                echo "\n\n" . self::$title . "\n\n";
                echo "[Last Query: " . ($this->last_query ? $this->last_query : "NULL") . "]\n";
                echo "[Last Function Call: " . $this->last_method . "]\n";
                echo "[Last Error: " . $this->last_error . "]\n";
                echo "[Last Results: " . print_r($this->last_result, true) . "]\n\n";

                break;
            case "json":
                $debugData = array(
                    "last_query" => $this->last_query,
                    "last_method" => $this->last_method,
                    "last_error" => $this->last_error,
                    "last_result" => $this->last_result
                );
                return json_encode($debugData);
                break;
            case "firephp":
                $debugDataTitle = array(
                    "last_query",
                    "last_method",
                    "last_error",
                    "last_result"
                );
                $debugData = array(
                    $this->last_query,
                    $this->last_method,
                    $this->last_error,
                    $this->last_result
                );
                if (class_exists("Debugger")) {
                    Debugger::table(array($debugDataTitle, $debugData), self::$title);
                }
                return null;
                break;
        }
        return null;
    }//end debug()


    /**
     * Escape a string just like mysql_escape_string which is now depreciated.
     *
     * @method escape($inp) Escape a string just like mysql_escape_string which is now depreciated.
     * @param  $inp
     *
     * @return string
     *
     * @author    Brian Tafoya
     * @copyright Copyright 2001 - 2017, Brian Tafoya.
     * @version   1.0
     */
    public function escape($inp)
    {
        if (!empty($inp) && is_string($inp)) {
            return (string)str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return (string)$inp;

    }//end escape()


    /**
     * get_col
     *
     * @method array|bool get_col() Return an array of a column's data.
     *
     * @param $queryString
     *
     * @return array|bool
     */
    public function get_col($queryString)
    {
        $this->last_query = $queryString;
        $this->last_method = "get_col";

        $start = microtime(true);
        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        $response = array();

        foreach (range(0, ($result->num_rows - 1)) AS $row_no) {
            $result->data_seek($row_no);
            $tmp = $result->fetch_array(MYSQLI_NUM);
            $response[] = $tmp[0];

        }

        if ($this->debug) {
            $this->results[(int)$this->debugCnt] = $response;
        }

        $this->debugCnt++;

        $this->last_result = $response;

        $result->close();
        $this->addLog($queryString, microtime(true) - $start);
        return $response;
    }//end get_col()


    /**
     * get_results
     *
     * @method array|bool get_results() Return query results.
     *
     * @param $queryString
     * @param string $output
     *
     * @return array|bool
     */
    public function get_results($queryString, $output = OBJECT)
    {
        $this->last_query = $queryString;
        $this->last_method = "get_results";

        $start = microtime(true);
        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_results",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_results",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_results",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        $response = array();

        if ($result->num_rows) {
            foreach (range(0, ($result->num_rows - 1)) AS $row_no) {
                $result->data_seek($row_no);

                if ($output == ARRAY_A) {
                    $response[] = $result->fetch_assoc();
                } elseif ($output == ARRAY_N) {
                    $response[] = $result->fetch_array(MYSQLI_NUM);
                } else {
                    $response[] = $result->fetch_object();
                }
            }
        }

        if ($this->debug) {
            $this->results[(int)$this->debugCnt] = $response;
        }

        $this->debugCnt++;

        $this->last_result = $response;

        $result->close();
        $this->addLog($queryString, microtime(true) - $start);
        return $response;
    }//end get_results()


    /**
     * get_row
     *
     * @method array|bool get_row() Return query row.
     *
     * @param $queryString
     * @param string $output
     *
     * @return array|bool|mixed|object|stdClass
     */
    public function get_row($queryString, $output = OBJECT)
    {
        $this->last_query = $queryString;
        $this->last_method = "get_row";

        $start = microtime(true);
        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if ($output == ARRAY_A) {
            $row = $result->fetch_assoc();
        } elseif ($output == ARRAY_N) {
            $row = $result->fetch_array(MYSQLI_NUM);
        } else {
            $row = $result->fetch_object();
        }

        if ($this->debug) {
            $this->results[(int)$this->debugCnt] = $row;
        }

        $this->debugCnt++;

        $this->last_result = $row;

        $result->close();
        $this->addLog($queryString, microtime(true) - $start);
        return $row;
    }//end get_row()


    /**
     * get_var
     *
     * @method bool|string get_var() Return query var.
     *
     * @param $queryString
     *
     * @return bool|string
     */
    function get_var($queryString)
    {
        $this->last_query = $queryString;
        $this->last_method = "get_var";

        $start = microtime(true);
        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_var",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_var",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_var",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }

        $row = $result->fetch_array(MYSQLI_NUM);

        $response = ($row && count($row) ? $row[0] : null);

        if ($this->debug) {
            $this->results[(int)$this->debugCnt] = $response;
        }

        $this->debugCnt++;

        $this->last_result = $response;

        $result->close();
        $this->addLog($queryString, microtime(true) - $start);
        return (string)$response;
    }//end get_var()


    /**
     * @return null
     */
    public function getLastError()
    {
        if (!empty($this->last_error)) {
            return $this->last_error;
        } else {
            return null;
        }

    }//end MySQLFirephpGetLastMysqlError()


    /**
     * @param $queryString
     * @param int $resultmode
     * @return bool
     */
    public function query($queryString, $resultmode = MYSQLI_STORE_RESULT)
    {
        $this->last_query = $queryString;
        $this->last_method = "query";

        $start = microtime(true);
        if ($this->datab->query($queryString, $resultmode) === true) {
            $this->addLog($queryString, microtime(true) - $start);
            // Take note of the insert_id
            if ($this->datab->insert_id) {
                $this->insert_id = $this->datab->insert_id;
            }

            $this->debugCnt++;

            $this->last_result = null;
            $this->addLog($queryString, microtime(true) - $start);
            return true;
        } else {

            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "query",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );

            $this->debugCnt++;
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }
    }//end query()


    /**
     * queryMulti
     *
     * @method bool queryMulti() Execute SQL Query.
     *
     * @param $queryString
     *
     * @return bool
     * @throws Exception
     */
    public function queryMulti($queryString)
    {
        if (!$this->datab) {
            throw new Exception("Connection is lost!");
        }

        $this->last_query = $queryString;
        $this->last_method = "query";

        $start = microtime(true);
        if ($this->datab->multi_query($queryString) === true) {

            // Take note of the insert_id
            if (preg_match("/^(insert|replace)\s+/i", $queryString)) {
                $this->insert_id = $this->datab->insert_id;
            }

            $this->debugCnt++;

            $this->last_result = null;
            $this->addLog($queryString, microtime(true) - $start);
            return true;
        } else {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "query",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error,
                    "queryString" => $queryString
                )
            );

            $this->debugCnt++;
            $this->addErrorLog($queryString, microtime(true) - $start, $this->datab->error);
            return false;
        }
    }//end queryMulti()


    /**
     * show_errors
     *
     * @method void show_errors() Enable error responses.
     */
    public function show_errors()
    {
        $this->show_errors = true;
    }//end show_errors()


    /**
     * hide_errors
     *
     * @method void hide_errors() Disable error responses.
     */
    public function hide_errors()
    {
        $this->show_errors = false;
    }//end hide_errors()


    /**
     * register_error
     *
     * @method void register_error() Record an error.
     *
     * @param $cnt
     * @param $errorData
     */
    public function register_error($cnt, $errorData)
    {

        if (class_exists("Debugger")) {
            Debugger::error($errorData, self::$title);
        }
        // Keep track of last error
        $this->last_error = $errorData["error"];

        $this->captured_errors[$cnt] = $errorData;
    }//end register_error()


    /**
     * @return array
     */
    public function getAllErrors()
    {
        return $this->captured_errors;
    }//end getAllErrors()


    /**
     *
     */
    public function cleadAllErrors()
    {
        $this->captured_errors = array();
    }//end cleadAllErrors()


    /**
     * register_error_callback
     *
     * @method void register_error_callback() Register a error handler.
     *
     * @param array $error_callback_method_array
     *
     * @throws Exception
     */
    function register_error_callback(array $error_callback_method_array)
    {

        if (!is_callable($error_callback_method_array)) {
            throw new Exception("Callback method not callable.");
        }

        $this->error_callback_method = $error_callback_method_array;

    }//end register_error_callback()


    /**
     * __callStatic
     *
     * @method void __callStatic() Static method handler.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func(__CLASS__ . '::' . $method, $parameters);
    }//end __callStatic()


    /**
     * Close the database connection
     */
    public function closeDB()
    {
        mysqli_close($this->datab);
    }

    /**
     * @return MySQLLib
     */
    public static function getDBInstance()
    {
        global $db;
        return $db;
    }

    /**
     * Retrieve from {@link Mysqli} the list of queries executed so far and return the list.
     * @return array[]
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Get total queries execution time
     * @return string
     */
    protected function getTotalTime()
    {
        $time = round(array_sum(array_column($this->getQueries(), 'time')), 4);
        return $time;
    }

    /**
     * Relay all calls.
     *
     * @param string $name The method name to call.
     * @param array $arguments The arguments for the call.
     *
     * @return mixed The call results.
     */
    public function __call($name, array $arguments)
    {
        return call_user_func_array(
            array($this, $name),
            $arguments
        );
    }

    /**
     * Add query to logged queries.
     * @param string $query
     */
    public function addLog($query, $time)
    {
        $entry = [
            'statement' => $query,
            'time' => $time
        ];
        array_push($this->queries, $entry);
    }

    /**
     * Add query to logged queries.
     * @param string $query
     */
    public function addErrorLog($query, $time, $errormessage)
    {
        $entry = [
            'statement' => $query,
            'time' => $time,
            'errormessage' => $errormessage
        ];
        array_push($this->queries, $entry);
    }

    /**
     * @return mysqli
     */
    public function returnInstance()
    {
        return $this->datab;
    }

    /**
     * @param $string
     * @param $array
     * @return string|string[]
     */
    public function prepareSql($string, $array)
    {

        $antiXss = new AntiXSS();

        //Get the key lengths for each of the array elements.
        $keys = array_map('strlen', array_keys($array));

        //Sort the array by string length so the longest strings are replaced first.
        array_multisort($keys, SORT_DESC, $array);

        foreach ($array as $k => $v) {
            //Quote non-numeric values.
            $replacement = is_numeric($v) ? $v : "'{$v}'";

            //Replace the needle.
            $string = str_replace(":" . $k, $antiXss->xss_clean($replacement), $string);
        }

        return $string;
    }
}
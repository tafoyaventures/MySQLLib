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
     * @var string $response_display_format Response format (html, text, json, or phpconsole).
     */
    public $response_display_format = "text";


    /**
     * OwpDBMySQLi constructor.
     *
     * @param $user
     * @param $pass
     * @param $db
     * @param $host
     *
     * @throws Exception
     */
    public function __construct($user, $pass, $db, $host)
    {
        try {
            $this->datab = new mysqli($host, $user, $pass, $db);

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
                echo "<h4>SQL Debug</h4><br>\n";
                echo "<b>Last Query</b> " . ($this->last_query ? $this->last_query : "NULL") . "<br>\n";
                echo "<b>Last Function Call:</b> " . $this->last_method . "<br>\n";
                echo "<b>Last Error:</b> " . $this->last_error . "<br>\n";
                echo "<b>Last Results:</b> " . $this->last_result . "<br>\n";

                return null;
                break;
            case "text":
                echo "\n\nSQL Debug\n\n";
                echo "[Last Query: " . ($this->last_query ? $this->last_query : "NULL") . "]\n";
                echo "[Last Function Call: " . $this->last_method . "]\n";
                echo "[Last Error: " . $this->last_error . "]\n";
                echo "[Last Results: " . print_r($this->last_result, true) . "]\n\n";

                return null;
                break;
            case "phpconsole":
                if (class_exists("PC")) {
                    $debugData = array(
                        "last_query" => $this->last_query,
                        "last_method" => $this->last_method,
                        "last_error" => $this->last_error,
                        "last_result" => $this->last_result
                    );

                    PC::debug($debugData, 'SQL Debug');
                }

                return null;
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
        }
    }//end debug()


    /**
     * Escape a string just like mysql_escape_string which is now depreciated.
     *
     * @method escape($inp) Escape a string just like mysql_escape_string which is now depreciated.
     * @param  $inp
     *
     * @return string
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

        if ($this->debug) {
            $this->queries[(int)$this->debugCnt] = $queryString;
        }

        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

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

        if ($this->debug) {
            $this->queries[(int)$this->debugCnt] = $queryString;
        }

        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_results",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_results",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_results",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

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

        if ($this->debug) {
            $this->queries[(int)$this->debugCnt] = $queryString;
        }

        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_row",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

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

        if ($this->debug) {
            $this->queries[(int)$this->debugCnt] = $queryString;
        }

        if (!$stmt = $this->datab->prepare($queryString)) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_var",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$stmt->execute()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_var",
                    "method" => "execute",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            return false;
        }

        if (!$result = $stmt->get_result()) {
            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "get_var",
                    "method" => "get_result",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

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

        return (string)$response;
    }//end get_var()


    /**
     * @param $queryString
     * @return bool
     */
    public function query($queryString)
    {

        $this->last_query = $queryString;
        $this->last_method = "query";

        if ($this->debug) {
            $this->queries[(int)$this->debugCnt] = $queryString;
        }

        if ($this->datab->query($queryString) === true) {

            // Take note of the insert_id
            if ($this->datab->insert_id) {
                $this->insert_id = $this->datab->insert_id;
            }

            $this->debugCnt++;

            $this->last_result = null;

            return true;
        } else {

            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "query",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            $this->debugCnt++;

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

        if ($this->debug) {
            $this->queries[(int)$this->debugCnt] = $queryString;
        }

        if ($this->datab->multi_query($queryString) === true) {

            // Take note of the insert_id
            if (preg_match("/^(insert|replace)\s+/i", $queryString)) {
                $this->insert_id = $this->datab->insert_id;
            }

            $this->debugCnt++;

            $this->last_result = null;

            return true;
        } else {

            $this->register_error(
                (int)$this->debugCnt, array(
                    "function" => "query",
                    "method" => "prepare",
                    "errno" => $this->datab->errno,
                    "error" => $this->datab->error
                )
            );

            $this->debugCnt++;

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
        // Keep track of last error
        $this->last_error = $errorData["error"];

        if ($this->debug) {
            // Capture all errors to an error array no matter what happens
            $this->captured_errors[$cnt] = $errorData;

            if ($this->show_errors) {
                // We will use an error callback so we can use PCConsole or another logger
                if ($this->error_callback_method) {
                    call_user_func($this->error_callback_method);
                } else {
                    switch ($this->response_display_format) {
                        default:
                            echo "<h4>SQL Error</h4><br>\n";
                            echo "<b>Last Query</b> " . ($this->last_query ? $this->last_query : "NULL") . "<br>\n";
                            echo "<b>Last Function Call:</b> " . $errorData["function"] . "<br>\n";
                            echo "<b>Last Method Call:</b> " . $errorData["method"] . "<br>\n";
                            echo "<b>Last Error:</b> " . $errorData["error"] . "<br>\n";
                            echo "<b>Last Error No:</b> " . $errorData["errno"] . "<br>\n";
                            break;
                        case "text":
                            echo "\n\nSQL Error\n\n";
                            echo "[Last Query: " . ($this->last_query ? $this->last_query : "NULL") . "]\n";
                            echo "[Last Function Call: " . $errorData["function"] . "]\n";
                            echo "[Last Method Call: " . $errorData["method"] . "]\n";
                            echo "[Last Error: " . $errorData["error"] . "]\n";
                            echo "[Last Error No: " . $errorData["errno"] . "]\n\n";
                            break;
                        case "phpconsole":
                            if (class_exists("PC")) {
                                $debugData = array(
                                    "last_query" => ($this->last_query ? $this->last_query : "NULL"),
                                    "last_function" => $errorData["function"],
                                    "last_method" => $errorData["method"],
                                    "last_error" => $errorData["error"],
                                    "last_errno" => $errorData["errno"]
                                );

                                PC::debug($debugData, 'SQL Debug');
                            };
                            break;
                    }
                }
            }
        }
    }//end register_error()


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
}
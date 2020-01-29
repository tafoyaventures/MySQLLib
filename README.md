# Tafoya Ventures MySQLi Support Library

 * @copyright 2001 - 2019, Brian Tafoya.
 * @package   MySQLLib
 * @author    Brian Tafoya <btafoya@tafoyaventures.com>
 * @version   1.0
 * @license   MIT
 * @license   https://opensource.org/licenses/MIT The MIT License
 * @category  MySQL_Support_Library
 * @link      https://tafoyaventures.com Tafoya Ventures

### Example

``
$db = new MySQLLib("{database_username}"),{database_password},{database_name},{database_server_hostname});
`` 

Execute a select, with the response in on of three formats: Numeric Array [ARRAY_N], Associative Array [ARRAY_A], or an Object [OBJECT] which is the default.  

``
$my_tables = $db->get_results(“SHOW TABLES”, ARRAY_N);
``

Execute a select which returns one row, with the response in on of three formats: Numeric Array [ARRAY_N], Associative Array [ARRAY_A], or an Object [OBJECT] which is the default.  

``
$my_tables = $db->get_row(“SELECT * FROM example LIMIT 1”, ARRAY_N);
``

Execute a select which returns one column, with the response in on of three formats: Numeric Array [ARRAY_N], Associative Array [ARRAY_A], or an Object [OBJECT] which is the default.  

``
$my_tables = $db->get_col(“SELECT example_col FROM example”, ARRAY_N);
``

Execute a select which returns one value.  

``
$my_tables = $db->get_var(“SELECT example_col FROM example LIMIT 1”);
``

Execute a query, INSERT returns ID when record created.

``
$newRowID = $db->query(“INSERT INTO example_table SET example_col = 1”);
``

Execute a multiple queries.

``
$db->queryMulti(“INSERT INTO example_table SET example_col = 1; INSERT INTO example_table SET example_col = 2;”);
``



Escape a text string.

``
$escaped_string = (string)$db->escape(“Hello Word”);
``


#### Developer
Brian Tafoya <btafoya@tafoyaventures.com>
https://tafoyaventures.com 

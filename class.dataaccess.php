<?php

/* ~class.dataaccess.php
 * 
 * @verson : 1.0
 * @contact : via mickesweb.se
 * @author :  Mikael Andersson <mikael@mickesweb.se>
 * @copyright (c) 2011, Mikael Andersson. All Rights Reserved.  
 * @license : http://creativecommons.org/licenses/by-nc-sa/3.0/
 * 
 * Last Updated: 2011-11-15
 * INFO: A class for linking between the database and page.
 * NOTE: Requires PHP version 5 or later
 * NOTE: Need the special class log (class.log.php) (if not use, set LOG to false)
 */

define("LOG", false);

define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASSWORD', 'password');
define('DB_TABLE', 'table');

if (LOG) {
    // Include only if debug is active.
    include_once(dirname(__FILE__) . '/class.log.php');
}

class Dataaccess {

    // @var pdo-object
    private $connect;
    // @var log-object
    private $log;
    // @var int
    private $lastId;

    /* Constructor, run when the new class is created.
     * Input:
     *      @param string $host
     *      @param string $username
     *      @param string $password
     *      @param string table
     */
    public function __construct($host=DB_HOST, $username=DB_USER, $password=DB_PASSWORD, $table=DB_TABLE) {
        if (LOG) {
            $this->log = new Log();
        }
        try {
            $this->connect = new PDO("mysql:host=$host;dbname=$table", $username, $password,
                            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            if (LOG) {
                $this->log->reportError($exception);
            }
            throw $exception;
        }
    }

    /* A function for insert things to database.
     * Input:
     *      @param string $sql (eg. INSERT INTO test(name) VALUES (:name))
     *      @param array $sqlValue can be Two-dimensional Array (eg. array(array(':name'=> $name),array(':name'=> $name2))
     * 
     * @return boolean
     */
    public function insert($sql, $sqlValue=array()) {
        try {
            $this->connect->beginTransaction();
            $insertString = $this->connect->prepare($sql);
            $checkArray = array_values($sqlValue);
            if(!empty($sqlValue) && is_array($checkArray[0])) {
                // Insert more then one row.
                foreach ($sqlValue as $sqls) {
                    $insertString->execute($sqls);
                }
            } else {
                $insertString->execute($sqlValue);
            }
            $this->lastId = $this->connect->lastInsertId();
            $resultBool = $this->connect->commit();
            // Roll back if the transaction did not go through.
            if (!$resultBool) {
                $this->connect->rollBack();
            }
            
            return $resultBool;
            
        } catch (Exception $exception) {
            $this->connect->rollBack();
            if (LOG) {
                $this->log->reportError($exception);
            }
            throw $exception;
        }
    }

    /* To delete data from database. Returns number of affected rows.
     * Input:
     *      @param string $sql (eg. DELETE FROM foo WHERE name = :name)
     *      @param array $sqlValue (eg. array(':name'=> $name))
     * 
     * @return int
     */
    public function delete($sql, $sqlValue=array()) {
        try {
            if (LOG) {
                $this->log->reportInfo($sql." <em>with</em> ".implode(',', array_keys($sqlValue))."=".implode(',', $sqlValue));
            }
            $this->connect->beginTransaction();
            $deleteString = $this->connect->prepare($sql);
            $deleteString->execute($sqlValue);
            $affectedRows = $deleteString->rowCount();
            $this->connect->commit();

            return $affectedRows;
            
        } catch (Exception $exception) {
            $this->connect->rollBack();
            if (LOG) {
                $this->log->reportError($exception);
            }
            throw $exception;
        }
    }

    /* Run a mysql query. Return all rows as a value array in the array.
     * Input:
     *      @param string $sql  (eg. SELECT * FROM foo WHERE name = :name)
     *      @param array $sqlValue (eg. array(':name' => $name))
     * 
     * @return array $resultarray
     */
    public function query($sql, $sqlValue=array()) {
        $resultArray = array();
        try {
            $result = $this->connect->prepare($sql);
            $result->execute($sqlValue);
            foreach ($result as $rows) {
                array_push($resultArray, $rows);
            }
            
            return $resultArray;
            
        } catch (Exception $exception) {
            if (LOG) {
                $this->log->reportError($exception);
            }
            throw $exception;
        }
    }

    /* Return ID at the last value in the database.
     *
     * @return int $lastId
     */
    public function lastId() {
        // TODO: Fix so you can run the function after the delete() and query() too.
        return $this->lastId;
    }

    /* Closes the data connection. */
    public function close() {
        $this->connect = null;
    }
}
?>
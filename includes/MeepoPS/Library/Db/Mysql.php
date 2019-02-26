<?php
/**
 * Created by lane
 * User: lane
 * Date: 16/5/25
 * Time: 上午11:10
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Library\Db;

use MeepoPS\Core\Log;

class Mysql{
    public static $conn = null;

    private $_host;
    private $_username;
    private $_password;
    private $_port;
    private $_dbName;

    /**
     * Mysql constructor.
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $dbName
     * @param string $port
     */
    public function __construct($host='', $username='', $password='', $dbName='', $port='3306')
    {
        if(is_null(self::$conn)){
            $this->_host = $host;
            $this->_username = $username;
            $this->_password = $password;
            $this->_port = $port;
            $this->_dbName = $dbName;
            $this->_connect();
        }
    }

    /**
     * 执行Sql语句
     * @param $sql
     */
    public function query($sql){
        $result = mysqli_query(self::$conn, $sql);
        if($result === false){
            self::$conn = null;
            $this->_connect();
        }
        return $result;
    }

    /**
     * 链接Mysql
     */
    private function _connect(){
        while(is_null(self::$conn = null)){
            self::$conn = mysqli_connect($this->_host, $this->_username, $this->_password, $this->_dbName, $this->_port);
            if(self::$conn && is_object(self::$conn)){
                break;
            }
            self::$conn = null;
            Log::write(__METHOD__.' Mysql connect failed', 'ERROR');
            sleep(10);
        }
    }
}
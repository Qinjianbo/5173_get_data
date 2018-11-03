<?php
namespace App\Models;

use App\DB;
use App\MyPdo;

class Model
{
    // 模型的table名
    protected $tableName = '';
    // 数据库
    protected $db = null;

    // 构造函数
    public function __construct($connectionType)
    {
        if ($connectionType == 'mysqli') {
            $this->db = (new DB());
        } elseif ($connectionType == 'pdo') {
            $this->db = (new MyPdo());
        } else {
            $this->db = null;
        }
    }

    /**
     * 指定tabel
     *
     * @param string $tableName
     * @throw \Exception
     */
    public function setTableName(string $tableName)
    {
        if ($tableName == '') {
            throw new \Exception('tableName can not be empty string');
        }
        $this->tableName = $tableName;
    }

    /**
     * 获取table名
     *
     * @return string
     */
    public function getTableName() : string
    {
        return $this->tableName;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        // 在对象销毁时关闭数据库连接
        if ($this->db) {
            $this->db->closeConnection();
            echo '数据库连接已关闭', PHP_EOL;
        }
    }
}

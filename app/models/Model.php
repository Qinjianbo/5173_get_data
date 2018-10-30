<?php
namespace App\Models;

class Model
{
    // 模型的table名
    protected $tableName = '';
    // 数据库
    protected $db = null;

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
}

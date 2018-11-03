<?php
namespace App;

class MyPdo
{
    private $dbName;
    private $host;
    private $user;
    private $pass;
    private $charset;
    private $connection;

    public function __construct()
    {
        $this->charset = 'UTF8';
    }

    /**
     * 设置数据库连接
     *
     * @param $config 数据库配置信息
     */
    public function setConnection($config)
    {
        $this->dbName = $config['db'];
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->pass = $config['pass'];

        echo $this->getDsn(), PHP_EOL;
        $this->connection = new \PDO($this->getDsn(), $this->user, $this->pass);
    }

    /**
     * 获取DSN字符串
     *
     * @return string
     */
    public function getDsn() : string
    {
        return sprintf(
            'mysql:dbname=%s;host=%s;charset=%s',
            $this->dbName,
            $this->host,
            $this->charset
        );
    }

    /**
     * 释放连接资源
     */
    public function closeConnection()
    {
        if($this->connection) {
            $this->connection = null;
        }
    }

    /**
     * 获取连接
     */
    public function getConnection()
    {
        return $this->connection;
    }
}

<?php
namespace App;
require(dirname(dirname(__FILE__)).'/config/dbConfig.php');

class DB
{
    // 数据库连接
    public $connection = null;

    /**
     * 设置指定数据库连接
     *
     * @param $config 配置信息
     * @throw \Exception
     */
    public function setConnection(array $config)
    {
        if (empty($config)) {
            throw new \Exception('Can not get config info!');
        }

        $this->connection = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['db']);
        if (!$this->connection) {
            throw new \Exception(mysqli_connect_error(), mysqli_connect_errno());
        }
    }

    /**
     * 设置数据库连接的编码方式
     *
     * @param string $charset 编码方式
     * @throw \Exception
     */
    public function setCharset(string $charset = 'utf-8')
    {
        if (!$charset) {
            throw new \Exception('Charset can not be empty string!');
        }

        if ($this->connection) {
            $this->connection->set_charset($charset);
        }
    }

    /**
     * 关闭数据库连接
     */
    public function closeConnection()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * 获取数据库连接
     *
     * @return
     */
    public function getConnection()
    {
        $this->setCharset();
        return $this->connection;
    }
}

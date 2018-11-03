<?php
namespace App\Models;

use App\Models\Model as BaseModel;

class MuDealHistoryModel extends BaseModel
{
    // 构造函数
    public function __construct($connectionType = 'mysqli')
    {
        parent::__construct($connectionType);
        require(dirname(dirname(dirname(__FILE__))).'/config/dbConfig.php');
        $this->db->setConnection($database['5173Data']);
        $this->setTableName('muDealHistory');
    }

    /**
     * 插入数据
     *
     * @param $data 要插入的数据
     *
     * @return int
     */
    // 知识点小记：设置自增之后，即使插入失败，id还是会自动增长
    // 知识点链接：https://www.cnblogs.com/zdz8207/p/3511147.html
    public function insert(array $data) : int
    {
        $fields = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES(%s)',
            $this->tableName,
            implode(',', $fields),
            implode(',', array_fill(0, count($fields), '?'))
        );
        $connection = $this->db->getConnection();
        $stmt = mysqli_prepare($connection, $sql);
        if (!$stmt) {
            throw new \Exception('Prepare sql error');
        }
        $types = '';
        foreach ($data as $d) {
            if (is_int($d)) {
                $types .= 'i';
            } elseif (is_float($d)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $args = [$stmt, $types];
        foreach ($data as $key => $value) {
            $args[] = &$data[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', $args);
        mysqli_stmt_execute($stmt);
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        if ($connection->errno) {
            echo 'Error:', $connection->error, PHP_EOL;
            return 0;
        }
        mysqli_stmt_close($stmt);

        return $affectedRows;
    }

    /**
     * 获取最新更新交易时间
     *
     * @return string
     */
    public function getLastDealTime() : string
    {
        $sql = 'SELECT dealTime FROM '.$this->getTableName().' ORDER BY dealTime DESC LIMIT 1';
        $connection = $this->db->getConnection();
        if ($result = $connection->query($sql)) {
            if($row = $result->fetch_assoc()) {
                return $row['dealTime'] ?? '';
            }
        } else {
            echo '时间查询出错', PHP_EOL;
            return '';
        }
    }

    /**
     * 通过Pdo的方式插入数据
     *
     * @param $data 要插入的数据
     * @return int
     */
    public function insertByPdoWay(array $data)
    {
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES(%s)',
            $this->tableName,
            implode(',', array_keys($data)),
            implode(',', array_fill(0, count($data), '?'))
        );
        $connection = $this->db->getConnection();
        $prepare = $connection->prepare($sql);
        if ($connection->errorCode() != '00000') {
            echo 'PrepareError:', ($connection->errorInfo())[2], PHP_EOL;
        }
        try {
            $connection->beginTransaction();
            $prepare->execute(array_values($data));
            if ($prepare->errorCode() != '00000') {
                echo 'ExecuteError:', ($prepare->errorInfo())[2], PHP_EOL;
            }
            $connection->commit();
            $lastInsertId = $connection->lastInsertId();
            if (!$lastInsertId) {
                return 0;
            }
        } catch(PDOException $e) {
            $connection->rollback();
            echo 'Error:', $e->getMessage(), PHP_EOL;
            return 0;
        } catch (Exception $e) {
            $connection->rollback();
            echo 'Error:', $e->getMessage(), PHP_EOL;
            return 0;
        }

        return 1;
    }
}

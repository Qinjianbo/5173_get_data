<?php
namespace App\Models;

use App\Models\Model as BaseModel;

class MuDealHistoryModel extends BaseModel
{
    // 构造函数
    public function __construct()
    {
        parent::__construct();
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
    public function insert(array $data) : int
    {
        $fields = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES(%s)',
            $this->tableName,
            implode(',', $fields),
            implode(',', array_fill(0, count($fields), '?'))
        );
        $stmt = mysqli_prepare($this->db->getConnection(), $sql);
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
}

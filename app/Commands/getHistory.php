<?php
namespace App\Commands;
require(dirname(dirname(dirname(__FILE__))).'/vendor/autoload.php');
require(dirname(dirname(dirname(__FILE__))).'/config/dbConfig.php');

use App\Service\GetHistory;
use App\Models\MuDealHistoryModel;

ini_set('date.timezone','Asia/Shanghai');
// 发送请求
echo 'start:', date('Y-m-d H:i:s'), PHP_EOL;
$page = 1;
$allProducts = [];
$url = 'http://trading.5173.com/list/viewlastestdeallist.aspx';
$params = [
    'ts' => -1,
    'offerid' => '',
    'gm' => '8bf9f68f4b7e4b7c9afcc3a257c60954',
    'raceid' => -1,
    'ga' => -1,
    'gs' => -1,
    'bt' => -1,
    'sort' => -1,
    'section' => '-1_-1',
    'ps' => 80,
    'k' => '',
];
$service = new GetHistory();
$service->setUrl($url);
$redisConfig = $redis['5173Data'];
$client = new \Predis\Client($redisConfig);
while(1) {
    echo '当前请求数据页数:', $page, PHP_EOL;
    $params['pg'] = $page;
    $html = $service->getHistoryData($params);
    echo '接收请求结果完毕...', PHP_EOL;

    echo '准备分析html...', PHP_EOL;
    $products = $service->analyzeHtml($html);
    echo 'html分析完毕...', PHP_EOL;
    echo '本次获取数据条数:', count($products), PHP_EOL;
    // 获取一下上一次获取到的最新一条的交易时间
    $lastDealTime = $client->get('lastDealTime') ?? 0;
    // 如果有最新交易时间，则过滤一下当前获取的的商品，只要这个时间之后的
    if ($lastDealTime) {
        foreach($products as $key => $product) {
            if (strtotime($product['dealTime']) > strtotime($lastDealTime)) {
                continue;
            }
            unset($products[$key]);
        }
    }
    // 再判断是不是空了，空了直接跳出
    if (empty($products)) {
        break;
    }
    $allProducts = array_merge($allProducts, $products);
    if (!$service->isLastPage($html)) {
        break;
    }
    $page++;
    sleep(1);
    echo PHP_EOL;
}
echo '共获取到数据条数:', count($allProducts), PHP_EOL;
//die('调试结束');

$failFile = sprintf('fail%s.csv', date('Ymd', time()));
if ($allProducts) {
    $successCount = writeToDatabase($allProducts, $failFile);
    if (count($allProducts) != $successCount) {
        echo "有写入失败的数据,请查看{$failFile}", PHP_EOL;
    }
    $lastDealTime = $allProducts[0]['dealTime'];
    $client->set('lastDealTime', $lastDealTime);
    $lastDealTime = $client->get('lastDealTime');
    echo '最新交易时间', $lastDealTime, PHP_EOL;
}

/**
 * 向数据库写入数据
 *
 * @param $allProducts 要写入的商品数据
 *
 * @return int
 */
function writeToDatabase($allProducts, $failFile) {
    echo PHP_EOL;
    echo '开始向数据库写入数据...', PHP_EOL;
    $muDealHistoryModel = new MuDealHistoryModel('pdo');
    //$muDealHistoryModel = new MuDealHistoryModel();
    $successCount = 0;
    $allProducts = array_reverse($allProducts);
    foreach ($allProducts as $product) {
        $product['created_at'] = date('Y-m-d H:i:s', time());
        try {
            //if($muDealHistoryModel->insert($product)) {
            if($muDealHistoryModel->insertByPdoWay($product)) {
                $successCount++;
            } else {
                file_put_contents($failFile, implode(',', $product).PHP_EOL, FILE_APPEND);
            }
        } catch(\Exception $e) {
            echo 'error:', $e->getMessage(), PHP_EOL;
            file_put_contents($failFile, implode(',', $product).PHP_EOL, FILE_APPEND);
        }
        usleep(10);
    }
    echo '成功插入数据条数:', $successCount, PHP_EOL;

    return $successCount;
}
echo 'end:', date('Y-m-d H:i:s'), PHP_EOL;

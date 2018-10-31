<?php
require(dirname(dirname(dirname(__FILE__))).'/vendor/autoload.php');

use App\Service\GetHistory;
use App\Models\MuDealHistoryModel;

// 发送请求
echo 'start...', PHP_EOL;
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
while(1) {
    echo '当前请求数据页数:', $page, PHP_EOL;
    $params['pg'] = $page;
    $html = $service->getHistoryData($params);
    echo '接收请求结果完毕...', PHP_EOL;

    echo '准备分析html...', PHP_EOL;
    $products = $service->analyzeHtml($html);
    if (empty($products)) {
        break;
    }
    echo 'html分析完毕...', PHP_EOL;
    echo '本次获取数据条数:', count($products), PHP_EOL;
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

echo PHP_EOL;
echo '开始向数据库写入数据...', PHP_EOL;
$muDealHistoryModel = new MuDealHistoryModel();
$successCount = 0;
foreach ($allProducts as $product) {
    if($muDealHistoryModel->insert($product)) {
        $successCount++;
    }
    usleep(10);
}
echo '成功插入数据条数:', $successCount, PHP_EOL;
echo 'end...';

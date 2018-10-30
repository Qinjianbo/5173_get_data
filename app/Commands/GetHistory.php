<?php
require("./vendor/autoload.php");

use App\Service\GetHistory;

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

echo '连接数据库...', PHP_EOL;
// 连接数据库，将数据存入数据库
$link = mysqli_connect($host, $user, $pass, $dbName);
if (!$link) {
    echo 'Error: Unable to connect to MySQL.', PHP_EOL;
    echo 'Debugging errno:', mysqli_connect_errno(), PHP_EOL;
    echo 'Debugging error:', mysqli_connect_error(), PHP_EOL;
}
$link->set_charset('utf8');
echo '数据库已连接...', PHP_EOL;

try {
    echo '开始像数据库写入数据...', PHP_EOL;
    $successCount = 0;
    foreach ($allProducts as $product) {
        $bool = $stmt = mysqli_prepare(
            $link,
            "INSERT INTO muDealHistory (name, type, price, dealTime, gameArea, link) VALUES(?, ?, ? ,? , ?, ?)"
        );
        if (!$bool) {
            die('stmt failed');
        }
        mysqli_stmt_bind_param(
            $stmt,
            'ssdsss',
            $product['name'],
            $product['type'],
            $product['price'],
            $product['dealTime'],
            $product['gameArea'],
            $product['link']
        );
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt)) {
            $successCount++;
        }
        mysqli_stmt_close($stmt);
        usleep(10);
    }
    echo '成功插入数据条数:', $successCount, PHP_EOL;
    $link->close();
    echo '数据库连接已关闭...', PHP_EOL;
} catch (\Exception $e) {
    $link_close();
    echo 'error:', $e->getMessage(), PHP_EOL;
}
echo 'end...';

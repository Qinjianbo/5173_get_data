<?php
require("./vendor/autoload.php");
require('./dbConfig.php');

use Sunra\PhpSimple\HtmlDomParser;

// 发送请求
echo 'start...', PHP_EOL;
echo '准备发起请求...', PHP_EOL;
$url = 'http://trading.5173.com/list/viewlastestdeallist.aspx?ts=&gm=8bf9f68f4b7e4b7c9afcc3a257c60954&ga=28184748865b44b8b0f62ae50fe32555&gs=7d0966cfac9e41e08489e94f0617c218';
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_HEADER, false);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($handle);
if (curl_errno($handle)) {
    echo 'curl error:', curl_error($handle), PHP_EOL; 
    exit;
}
$result =iconv('gb2312', 'utf-8', $result);
echo '接收请求结果完毕...', PHP_EOL;

echo '准备分析html...', PHP_EOL;
// 分析返回html,拿到想要的数据
$dom = HtmlDomParser::str_get_html($result);
$equipments = $dom->find('div.listmain');
$products = [];
foreach ($equipments as $equipment) {
    $data = [];
    $items = $equipment->find('ul');
    $data['name'] = $items[0]->find('a', 0)->plaintext;
    $data['gameArea'] = $items[0]->find('li', 1)->plaintext;
    $data['gameArea'] = trim(str_replace('游戏/区/服/阵营：奇迹MU/', '', $data['gameArea']));
    $data['type'] = $items[1]->find('li', 0)->plaintext;
    $data['price'] = $items[2]->find('li', 0)->plaintext;
    $data['price'] = str_replace(',', '', trim(str_replace('元', '', $data['price'])));
    $data['dealTime'] = $items[4]->find('li', 0)->plaintext;
    $products[] = $data;
}
echo 'html分析完毕...', PHP_EOL;
echo '共获取到数据条数:', count($products), PHP_EOL;

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
    foreach ($products as $product) {
        $bool = $stmt = mysqli_prepare(
            $link,
            "INSERT INTO muDealHistory (name, type, price, dealTime, gameArea) VALUES(?, ?, ? ,? ,?)"
        );
        if (!$bool) {
            die('stmt failed');
        }
        mysqli_stmt_bind_param(
            $stmt,
            'ssdss',
            $product['name'],
            $product['type'],
            $product['price'],
            $product['dealTime'],
            $product['gameArea']
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

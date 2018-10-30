<?php
require("./vendor/autoload.php");
require('./dbConfig.php');

use Sunra\PhpSimple\HtmlDomParser;

// 发送请求
echo 'start...', PHP_EOL;
$page = 1;
$allProducts = [];
while(1) {
    echo '当前请求数据页数:', $page, PHP_EOL;
    $url = sprintf('http://trading.5173.com/list/viewlastestdeallist.aspx?'.
        'ts=-1&offerid=&gm=8bf9f68f4b7e4b7c9afcc3a257c60954&raceid=-1&ga=-1&gs=-1&bt=-1&sort=-1&section=-1_-1&'.
        'pg=%d'.
        '&ps=80&k=', $page);
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_HEADER, false);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($handle);
    if (curl_errno($handle)) {
        echo 'curl error:', curl_error($handle), PHP_EOL; 
        exit;
    }
    $result = iconv('gb2312', 'utf-8//IGNORE', $result);
    echo '接收请求结果完毕...', PHP_EOL;
    
    echo '准备分析html...', PHP_EOL;
    // 分析返回html,拿到想要的数据
    $dom = HtmlDomParser::str_get_html($result);
    $equipments = $dom->find('div.listmain');
    $products = [];
    foreach ($equipments as $key => $equipment) {
        $data = [];
        $items = $equipment->find('ul');
        $data['name'] = $items[0]->find('a', 0)->plaintext;
        $data['link'] = $items[0]->find('a', 0)->href;
        $data['gameArea'] = $items[0]->find('li', 1)->plaintext;
        $data['gameArea'] = trim(str_replace('游戏/区/服/阵营：奇迹MU/', '', $data['gameArea']));
        $data['type'] = $items[1]->find('li', 0)->plaintext;
        $data['price'] = $items[2]->find('li', 0)->plaintext;
        $data['price'] = str_replace(',', '', trim(str_replace('元', '', $data['price'])));
        $data['dealTime'] = $items[4]->find('li', 0)->plaintext;
        $products[] = $data;
    }
    if (empty($products)) {
        break;
    }
    echo 'html分析完毕...', PHP_EOL;
    echo '本次获取数据条数:', count($products), PHP_EOL;
    $allProducts = array_merge($allProducts, $products);
    if (!empty($dom->find('li.down_off'))) {
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

<?php
require("./vendor/autoload.php");

use Sunra\PhpSimple\HtmlDomParser;

// 发送请求
$url = 'http://trading.5173.com/list/viewlastestdeallist.aspx?ts=&gm=8bf9f68f4b7e4b7c9afcc3a257c60954&ga=28184748865b44b8b0f62ae50fe32555&gs=7d0966cfac9e41e08489e94f0617c218';
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_HEADER, false);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($handle);
$result =iconv('gb2312', 'utf-8', $result);

// 分析返回html,拿到想要的数据
$dom = HtmlDomParser::str_get_html($result);
$equipments = $dom->find('div.listmain');
$data = [];
foreach ($equipments as $equipment) {
    $items = $equipment->find('ul');
    $data['name'] = $items[0]->find('a', 0)->plaintext;
    $data['gameArea'] = $items[0]->find('li', 1)->plaintext;
    $data['gameArea'] = trim(str_replace('游戏/区/服/阵营：奇迹MU/', '', $data['gameArea']));
    $data['type'] = $items[1]->find('li', 0)->plaintext;
    $data['price'] = $items[2]->find('li', 0)->plaintext;
    $data['price'] = str_replace(',', '', trim(str_replace('元', '', $data['price'])));
    $data['dealTime'] = $items[4]->find('li', 0)->plaintext;
    print_r($data);
}


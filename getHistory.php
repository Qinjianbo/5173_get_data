<?php
require("./vendor/autoload.php");

use Sunra\PhpSimple\HtmlDomParser;

$url = 'http://trading.5173.com/list/viewlastestdeallist.aspx?ts=&gm=8bf9f68f4b7e4b7c9afcc3a257c60954&ga=28184748865b44b8b0f62ae50fe32555&gs=7d0966cfac9e41e08489e94f0617c218';
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_HEADER, false);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($handle);
$result =iconv('gb2312', 'utf-8', $result);

$dom = HtmlDomParser::str_get_html($result);

$equipments = $dom->find('div.listmain');
$data = [];
foreach ($equipments as $equipment) {
    $items = $equipment->find('ul');
    $data['name'] = $items[0]->find('a', 0)->plaintext;
    $data['type'] = $items[1]->find('li', 0)->plaintext;
    $data['price'] = $items[2]->find('li', 0)->plaintext;
    $data['dealTime'] = $items[4]->find('li', 0)->plaintext;
}


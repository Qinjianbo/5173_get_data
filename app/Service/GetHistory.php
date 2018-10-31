<?php
namespace App\Service;

use Sunra\PhpSimple\HtmlDomParser;
use App\Models\MuDealHistoryModel;

class GetHistory
{
    // url 信息
    private $url;
    /**
     * 设置请求的url
     *
     * @param $url 请求url地址
     */
    public function setUrl($url)
    {
        if (!$url) {
            throw new \Exception('Url can not be empty string!');
        }
        $this->url = $url;
    }

    /**
     * 获取历史交易数据
     *
     * @param $parmas 参数信息
     *
     * @return
     */
    public function getHistoryData(array $params)
    {
        echo 'getHistoryDataBegin...', PHP_EOL;
        $query = http_build_query($params);
        $url = sprintf('%s?%s', $this->url, $query);
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
        echo 'getHistoryDataEnd...', PHP_EOL;

        return $result;
    }

    /**
     * 分析html拿商品数据
     *
     * @param $html 待分析的html
     *
     * @return array
     */
    public function analyzeHtml(string $html) : array
    {
        echo 'analyzeHtmlBegin...', PHP_EOL;
        $dom = HtmlDomParser::str_get_html($html);
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
            $data['price'] = floatval(str_replace(',', '', trim(str_replace('元', '', $data['price']))));
            $data['dealTime'] = $items[4]->find('li', 0)->plaintext;
            $products[] = $data;
        }

        echo 'analyzeHtmlEnd...', PHP_EOL;
        return $products;
    }

    /**
     * 检查是否已经到最后一页
     *
     * @param $html
     *
     * @return bool
     */
    public function isLastPage(string $html) : bool
    {
        $dom = HtmlDomParser::str_get_html($html);

        return empty($dom->find('li.down_off'));
    }
}

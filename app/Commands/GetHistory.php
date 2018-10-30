<?php
require(dirname(dirname(dirname(__FILE__))).'/vendor/autoload.php');

use App\Models\MuDealHistoryModel;

class GetHistory
{
    public function test()
    {
        $muDealHistoryModel = new MudealHistoryModel();
        $muDealHistoryModel->setTableName('muDealHistory');
        echo $muDealHistoryModel->getTableName();
    }
}

$getHistory = new GetHistory();
$getHistory->test();

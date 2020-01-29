<?php

use Service\Companies\AdminCompany\CancelPremium;
use Service\Companies\AdminCompany\ChangeClientType;
use Service\Companies\AdminCompany\ChangeFreeStatus;
use Service\Companies\AdminCompany\HistoryCompany;
use Service\Companies\AdminCompany\HistoryCompanyDownloadXls;
use Service\Companies\AdminCompany\UpdateHistoryComments;
use Utils\XlsWritterPhpOffice;
use ValueObject\Proveedores\CancelationReasons;

class AdminCompany extends Admin {


    public function clientType() {
        $changeClientType = new ChangeClientType(UsuariosAdmin::getById($_SESSION['id']));
        echo JsonResponse::fromCreatedCode([$changeClientType()])->toJson();
    }

    public function changeFree() {

        $comId = Request::getParam('com');
        $value = Request::getParam('value') ? 1: 0;

        $changeFree = new ChangeFreeStatus($comId, $value, UsuariosAdmin::getById($_SESSION['id']));
        if($changeFree()){
            echo JsonResponse::fromNoContentCode([])->toJson();
        }  else {
            echo JsonResponse::fromUnprocessableCode([])->toJson();
        }

    }
    public function cancelPremium() {
        $reasons = CancelationReasons::fromArray(
            [
                'comId' => Request::getParam('comId'),
                'type' => Request::getParam('cancelReasonId'),
                'comments' => Request::getParam('cancelReasonTxt'),

            ]
        );
        $cancelPremium = new CancelPremium($reasons);
        echo JsonResponse::fromCreatedCode(['cancelPremium' => $cancelPremium()])->toJson();

    }

    public function getHistoryList() {

        $comId = Request::getParam('comId');
        try {
            $historyCompany = HistoryCompany::fromCompanyIdFilter($comId);
            $history = $historyCompany();
            echo JsonResponse::fromAcceptedCode($history)->toJson();
        } catch (Exception $e) {
            echo JsonResponse::fromUnprocessableCode([])->toJson();
        }


    }

    public function getHistoryListByDay() {
        $companyClientHistoryId = Request::getParam('rowId');
        $historyCompany = HistoryCompany::fromHistoryRowId($companyClientHistoryId);
        $history = $historyCompany();
        echo JsonResponse::fromAcceptedCode($history)->toJson();
    }

    public function historyxls() {
        $comId = Request::getParam('comId');
        $xls = true;
        $history = HistoryCompany::fromCompanyId($comId, $xls);
        $filename = 'history_'.DateUtils::now(DateUtils::TZ_EUROPE_MADRID, DateUtils::FORMAT_FILE_NAME).'_ '. $comId;
        $historyCompanyXls = new HistoryCompanyDownloadXls($history, $filename);
        //TODO: Convertir en ValueObject
        $cellFormat = [
            'money'=> ['J','M'],
            //'string'=>[]
        ];
        $xlsWritter = new XlsWritterPhpOffice($cellFormat);
        $historyCompanyXls($xlsWritter);
    }

    public function download_full_history() {
        $xls = true;
        $history = HistoryCompany::fromAllRows($xls);
        $filename = 'full_history_'.DateUtils::now(DateUtils::TZ_EUROPE_MADRID, DateUtils::FORMAT_FILE_NAME);
        $historyCompanyXls = new HistoryCompanyDownloadXls($history, $filename);
        //TODO: Convertir en ValueObject
        $cellFormat = [
            'money'=> ['J','M'],
            //'string'=>[]
        ];
        $xlsWritter = new XlsWritterPhpOffice($cellFormat);
        $historyCompanyXls($xlsWritter);
    }

    public function download_last_history_period() {
        $xls = true;
        $history = HistoryCompany::fromAllRowsFilter($xls);
        $filename = 'last_history_'.DateUtils::now(DateUtils::TZ_EUROPE_MADRID, DateUtils::FORMAT_FILE_NAME);
        $historyCompanyXls = new HistoryCompanyDownloadXls($history, $filename);
        //TODO: Convertir en ValueObject
        $cellFormat = [
            'money'=> ['J','M'],
            //'string'=>[]
        ];
        $xlsWritter = new XlsWritterPhpOffice($cellFormat);
        $historyCompanyXls($xlsWritter);
    }

    public function putHistoryComments() {

        $data = json_decode(file_get_contents("php://input"),true);
        $updateHistoryComments = new UpdateHistoryComments();
        $updateHistoryComments($data);

        echo JsonResponse::fromAcceptedCode([])->toJson();
    }

    public function visitCompany() {
       $comId = Request::getParam('comId');
       $check = Request::getParam('check');
       $check = CheckVisits::checkFromCompany($comId, $check);
       $check();
       echo JsonResponse::fromNoContentCode([])->toJson();
    }
}

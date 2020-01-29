<?php


namespace Service\FirstInteraction;
use Service\FirstInteraction\GetTimeFromFirstInteraction;

final class GetAverageFirstInteractionWorkingDays
{
    private $comId;
    private $interval;
    private $showAlsoErrorRequests;
    private $precision;

    public function __construct(int $company_id, int $interval, int $precision = 2, bool $showAlsoErrorRequests = false)
    {
        $this->comId = $company_id;
        $this->interval = $interval;
        $this->showAlsoErrorRequests = $showAlsoErrorRequests;
        $this->precision = $precision;
    }

    public function __invoke(): string
    {
        $requests = \RequestModel::getCompanyRequestsFirstInteraction($this->comId, $this->interval, $this->showAlsoErrorRequests);
        $total = 0;
        if(!empty($requests)) {

            foreach ($requests as $request) {
                $getTimeFromFirstInteraction =  GetTimeFromFirstInteraction::createToGetRawData($request['req_date_validate'], $request['first_interaction_time']);
                $total = $total + $getTimeFromFirstInteraction();
            }
        }

        if($total > 0) {
            $average = $total / count($requests);
        }

        $average = number_format(round($average, $this->precision), $this->precision,',','.');
        return $average;
    }
}
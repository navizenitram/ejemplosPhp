<?php


namespace Service\FirstInteraction;


use RequestModel;
use Service\FirstInteraction\GetAverageFirstInteractionWorkingDays;

final class GetFirstInteractionQualitySealData
{
    const FI_LABEL_TOP_24 = 'FI_LABEL_TOP24';
    const FI_LABEL_FAST = 'FI_LABEL_FAST';
    const FI_LABEL_SLOW = 'FI_LABEL_SLOW';
    const FI_LABEL_WARNING = 'FI_LABEL_WARNING';

    const AVERAGE_BT_0_1 = 1;
    const AVERAGE_BT_1_2 = 2;
    const AVERAGE_BT_2_5 = 5;

    const MAX_PERCENT_THRESHOLD = 90;
    const MEDIUM_PERCENT_THRESHOLD = 80;
    const MIN_PERCENT_THRESHOLD = 65;


    private $comId;
    private $percent;
    private $average;
    private $externalData;

    private function __construct(int $comId, float $percent, int $average, bool $externalData)
    {
        $this->comId = $comId;
        $this->percent = round($percent,0);
        $this->average = $average;
        $this->externalData = $externalData;
    }

    public static function createFromCompanyIdWithData(int $comId, float $percent, int $average) : GetFirstInteractionQualitySealData
    {
        $withExternalData = true;
        $seal = new self($comId, $percent, $average, $withExternalData);
        return $seal;
    }

    public static function createFromCompanyId(int $comId) : GetFirstInteractionQualitySealData
    {
        $hasToGenerateData = false;
        $seal = new self($comId, 0, 0, $hasToGenerateData);
        return $seal;
    }


    public function __invoke()
    {
        if(!$this->externalData) {
            list($percent, $average) = $this->getData();
        } else {
            $percent = $this->percent;
            $average = $this->average;
        }

        if($average<= self::AVERAGE_BT_0_1) { 
            if($percent >= self::MAX_PERCENT_THRESHOLD) {
                $label = self::FI_LABEL_TOP_24;
            } else if ($percent >= self::MEDIUM_PERCENT_THRESHOLD) {
                $label = self::FI_LABEL_FAST;
            } else if($percent>=self::MIN_PERCENT_THRESHOLD) {
                $label = self::FI_LABEL_SLOW;
            } else {
                $label = self::FI_LABEL_WARNING;
            }
        } else if($average <= self::AVERAGE_BT_1_2){ 

            if($percent >= self::MEDIUM_PERCENT_THRESHOLD) {
                $label = self::FI_LABEL_FAST;
            } else if ($percent >= self::MIN_PERCENT_THRESHOLD) {
                $label = self::FI_LABEL_SLOW;
            } else {
                $label = self::FI_LABEL_WARNING;
            }

        } else if($average <= self::AVERAGE_BT_2_5) { 
            if ($percent >= self::MIN_PERCENT_THRESHOLD) {
                $label = self::FI_LABEL_SLOW;
            } else {
                $label = self::FI_LABEL_WARNING;
            }
        } else { 
            $label = self::FI_LABEL_WARNING;
        }

        
        $seal = [
            'label'=>$label,
            'percent'=>$percent,
            'average'=>$average,
        ];

        return $seal;

    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $totalRequest = RequestModel::countCompanyRequests($this->comId, '30 DAY', RequestModel::SHOW_ONLY_REQUESTS_NO_ERROR);
        $firstInteractionRequestTotal = RequestModel::countCompanyRequestsFirstInteraction($this->comId, '30 DAY', RequestModel::SHOW_ONLY_REQUESTS_NO_ERROR);
        $percent = ($firstInteractionRequestTotal > 0) ? (($firstInteractionRequestTotal * 100) / $totalRequest) : 0;
        $percent = round($percent, 0);
        $firstInteractionAverage = new GetAverageFirstInteractionWorkingDays($this->comId, '30 DAY', 2, RequestModel::SHOW_ONLY_REQUESTS_NO_ERROR);
        $average = $firstInteractionAverage();
        return array($percent, $average);
    }
}
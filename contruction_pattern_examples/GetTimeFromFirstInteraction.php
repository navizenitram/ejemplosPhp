<?php
namespace Service\FirstInteraction;

use DateInterval;
use DateTimeZone;
use DateTime;
use ValueObject\Proveedores\DateTimeProveedores;
final class GetTimeFromFirstInteraction
{

    private $validationDate;
    private $interactionDate;
    private $formatTime;
    private $toString;
    private $precision;

    const WORKING_HOURS = 0;
    const WORKING_HOURS_UP = 1;
    const WORKING_HOURS_DOWN = -1;

    const WORK_DAY_START_HOUR_LOCAL_TIME = 9;
    const WORK_DAY_END_HOUR_LOCAL_TIME = 19;

    const LIMIT_DAYS = 30;

    private function __construct(string $validationDate, string $interactionDate, string $formatTime = 'DAYS', int $precision = 2, bool $toString = true)
    {
        $this->formatTime = $formatTime;
        $this->validationDate = new DateTimeProveedores($validationDate);
        $this->interactionDate = new DateTimeProveedores($interactionDate);
        $this->toString = $toString;
        $this->precision = $precision;
    }

    public static function createToGetRawData(string $validationDate, string $interactionDate, int $precision) : GetTimeFromFirstInteraction
    {
        $formatInDays= 'DAYS';
        $dataInDecimal = false;
        $getTimeFromFirstInteraction = new self($validationDate, $interactionDate, $formatInDays, $precision, $dataInDecimal);
        return $getTimeFromFirstInteraction;
    }

    public function __invoke()
    {
        $inHours = false;
        if($this->formatTime == 'HOURS') {
            $inHours = true;
        }

        return self::getDecimalTime($this->validationDate->format(\DateUtils::FORMAT_MYSQL), $this->interactionDate->format(\DateUtils::FORMAT_MYSQL), $this->precision, $inHours, $this->toString);
    }

    private static function getDecimalTime(string $dateValidation, string $dateInteraction, int $precision = 1, bool $inHours = false, bool $toString = true) {

        $horas = self::getWorkingTime($dateValidation, $dateInteraction);
        if(!$inHours) {
            $horas = self::toWorkingDays($horas);
        }

        if($toString) {
            $decimalTime = number_format(round($horas,$precision),$precision,',','.');
        } else {
            $decimalTime = round($horas,$precision);
        }

        return $decimalTime;

    }

    private static function getWorkingTime(string $dateValidation, string $dateInteraction, int $workday_start_hour = self::WORK_DAY_START_HOUR_LOCAL_TIME, int $workday_end_hour = self::WORK_DAY_END_HOUR_LOCAL_TIME, string $timeZone = 'Europe/Madrid') {
        $workingHours = self::workingHours($dateValidation, $workday_start_hour, $workday_end_hour, $timeZone);
        $weekDay = self::getWeekDay($dateValidation);


        if ($weekDay > 5) {
            $plusDays = (7 - $weekDay) + 1;
            $efectiveDateValidation = self::nextWorkingDay($dateValidation, $timeZone, $plusDays);
        } elseif ($weekDay == 5 && $workingHours == self::WORKING_HOURS_UP) {
            $efectiveDateValidation = self::nextWorkingDay($dateValidation, $timeZone, 3);
        } elseif ($workingHours == self::WORKING_HOURS_UP) {
            $efectiveDateValidation = self::nextWorkingDay($dateValidation, $timeZone, 1);
        } elseif ($workingHours == self::WORKING_HOURS_DOWN) {
            $efectiveDateValidation = self::nextWorkingDay($dateValidation, $timeZone, 0);
        } else {
            $efectiveDateValidation = $dateValidation;
        }

        $workingHours = self::workingHours($dateInteraction, $workday_start_hour, $workday_end_hour, $timeZone);
        $weekDay = self::getWeekDay($dateInteraction);

        if ($weekDay > 5) {
            $subDays = ($weekDay - 5);
            $efectiveDateInteraction = self::prevWorkingDay($dateInteraction, $timeZone, $subDays);
        } elseif ($weekDay == 5 && $workingHours == self::WORKING_HOURS_UP) {
            $efectiveDateInteraction = self::prevWorkingDay($dateInteraction, $timeZone, 0);
        } elseif ($workingHours == self::WORKING_HOURS_UP) {
            $efectiveDateInteraction = self::prevWorkingDay($dateInteraction, $timeZone, 0);
        } elseif ($workingHours == self::WORKING_HOURS_DOWN) {
            $efectiveDateInteraction = self::prevWorkingDay($dateInteraction, $timeZone, 1);
        } else {
            $efectiveDateInteraction = $dateInteraction;
        }


        if($efectiveDateInteraction < $efectiveDateValidation) {
            return 0;
        } else {
            return self::get_working_hours($efectiveDateValidation, $efectiveDateInteraction, $workday_start_hour, $workday_end_hour);
        }



    }

    private static function workingHours($data,$start,$end,$timeZone) {
        // create a $dt object with the UTC timezone
        $dt = new DateTime($data, new DateTimeZone('UTC'));
        // change the timezone of the object without changing it's time
        $dt->setTimezone(new DateTimeZone($timeZone));
        // format the datetime
        $hora = $dt->format('H');
        $minuto = $dt->format('i');

        if($hora == $end && $minuto > 0) {
            $hora = $hora + 1;
        }

        if($hora >= $start && $hora <= $end) {
            $workingHours = self::WORKING_HOURS;
        } else if ($hora > $end) {
            $workingHours = self::WORKING_HOURS_UP;
        } else if ($hora < $start) {
            $workingHours = self::WORKING_HOURS_DOWN;
        }

        return $workingHours;
    }

    private static function nextWorkingDay($date, $timeZone, $days) {
        $dt = new DateTime($date, new DateTimeZone('UTC'));
        // change the timezone of the object without changing it's time
        $dt->setTimezone(new DateTimeZone($timeZone));
        $dt->add(new DateInterval('P' . $days . 'D'));
        $efectiveDate = $dt->format('Y-m-d 09:00:00');

        $dt = new DateTime($efectiveDate, new DateTimeZone($timeZone));
        // change the timezone of the object without changing it's time
        $dt->setTimezone(new DateTimeZone('UTC'));

        return $dt->format('Y-m-d H:i:s'); //Primer dia hábil de validación.
    }

    private static function prevWorkingDay($date, $timeZone, $days) {
        $dt = new DateTime($date, new DateTimeZone('UTC'));
        // change the timezone of the object without changing it's time
        $dt->setTimezone(new DateTimeZone($timeZone));
        $dt->sub(new DateInterval('P' . $days . 'D'));
        $efectiveDate = $dt->format('Y-m-d 19:00:00');

        $dt = new DateTime($efectiveDate, new DateTimeZone($timeZone));
        // change the timezone of the object without changing it's time
        $dt->setTimezone(new DateTimeZone('UTC'));

        return $dt->format('Y-m-d H:i:s'); //Primer dia hábil de validación.
    }

    private static function getWeekDay($date) {

        $weekDay = date("w", strtotime($date));
        if ($weekDay == 0) {
            $weekDay = 7;
        }
        return $weekDay;
    }

    private static function get_working_hours($from, $to, $workday_start_hour, $workday_end_hour) {
        // timestamps
        $from_timestamp = strtotime($from);
        $to_timestamp = strtotime($to);

        // work day seconds

        $workday_seconds = ($workday_end_hour - $workday_start_hour) * 3600;

        // work days beetwen dates, minus 1 day
        $from_date = date('Y-m-d', $from_timestamp);
        $to_date = date('Y-m-d', $to_timestamp);
        $workdays_number = count(self::get_workdays($from_date, $to_date)) - 1;
        $workdays_number = $workdays_number < 0 ? 0 : $workdays_number;

        // start and end time
        $start_time_in_seconds = date("H", $from_timestamp) * 3600 + date("i", $from_timestamp) * 60;
        $end_time_in_seconds = date("H", $to_timestamp) * 3600 + date("i", $to_timestamp) * 60;

        // final calculations
        $working_hours = ($workdays_number * $workday_seconds + $end_time_in_seconds - $start_time_in_seconds) / 86400 * 24;

        return $working_hours;
    }

    private static function get_workdays($from, $to) {
        // arrays
        $days_array = array();
        $skipdays = array("Saturday", "Sunday");
        $skipdates = self::get_holidays();

        // other variables
        $i = 0;
        $current = $from;

        if ($current == $to) // same dates
        {
            $timestamp = strtotime($from);
            if (!in_array(date("l", $timestamp), $skipdays) && !in_array(date("Y-m-d", $timestamp), $skipdates)) {
                $days_array[] = date("Y-m-d", $timestamp);
            }
        } elseif ($current < $to) // different dates
        {
            while ($current < $to) {
                $timestamp = strtotime($from . " +" . $i . " day");
                if (!in_array(date("l", $timestamp), $skipdays) && !in_array(date("Y-m-d", $timestamp), $skipdates)) {
                    $days_array[] = date("Y-m-d", $timestamp);
                }
                $current = date("Y-m-d", $timestamp);
                $i++;
            }
        }

        return $days_array;
    }

    private static function get_holidays() {
        // arrays
        $days_array = array();

        // You have to put there your source of holidays and make them as array...
        // For example, database in Codeigniter:
        // $days_array = $this->my_model->get_holidays_array();

        return $days_array;
    }

    /**
     * Convierte las horas laborables en días laborables.
     * Si los días superan los 30 día como máximo devuelve 30 días.
     */
    private static function toWorkingDays($horas)
    {
        $dias = $horas / (self::WORK_DAY_END_HOUR_LOCAL_TIME - self::WORK_DAY_START_HOUR_LOCAL_TIME);
        if ($dias > self::LIMIT_DAYS) {
            $dias = self::LIMIT_DAYS;
        }

        return $dias;
    }


}
<?php
namespace App\Services;

class UtilityService extends SettingsService {

    public function uniqueReference() {
        return date("YmdHis").random_int(1010, 10000);
    }

    public function dateCreated() {
        return date("Y-m-d H:i:s");
    }

    public function niceDateFormat($date, $format="date_time") {

        if ($format == "date_time") {
            $format = "D j, M Y h:ia"; 
        } else {
            $format = "D j, M Y";
        }

        $timestamp = strtotime($date);
        $niceFormat = date($format, $timestamp);

        return $niceFormat;
    }

    public function defaultPlanId() {
        return $this->getAllSettings()->default_plan_id;
    }

    public function monnifyInfo() {
        return $this->getAllSettings()->monnify;
    }

    public function AirtimeInfo() {
        return $this->getAllSettings()->airtime_info;
    }
}
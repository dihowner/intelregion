<?php
namespace App\Services;
use Illuminate\Http\Request;
use Validator;

use App\Models\Settings;

class SettingsService  {
    protected $responseBody;

    public function getAllSettings() {
        $allSettings = Settings::all();
        if($allSettings->count() > 0) {
            foreach($allSettings as $index => $value) {
                $feedback[$value['name']] = $value['content'];
            }
            $this->responseBody = (object)($feedback);
        } else {
            $this->responseBody = false;
        }
        return $this->responseBody;
    }
}
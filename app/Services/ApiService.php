<?php
namespace App\Services;

use Exception;
use App\Models\Api;
use App\Http\Traits\ResponseTrait;
use App\Models\Vendor;

class ApiService {

    use ResponseTrait;
    protected $responseBody, $utilityService;

    public function __construct(UtilityService $utilityService) {
        $this->utilityService = $utilityService;
    }
    
    public function getApi(int $apiId) {
        try {
            $fetchApi = Api::where("id", $apiId)->first();
            if($fetchApi != NULL) {
                $fetchApi['api_vendor'] = self::getVendor($fetchApi->api_vendor_id);
                unset($fetchApi->api_vendor_id);
                $this->responseBody = $fetchApi; 
            }
            else {
                $this->responseBody = false; 
            }
        }
        catch(Exception $e) {
           $this->responseBody = $this->sendError("Error", "Error processing request", 500); 
        }
        return $this->responseBody;
    }

    private function getVendor(int $vendorId) {
        try {
            $fetchVendor = Vendor::where("id", $vendorId)->first();
            if($fetchVendor != NULL) {
                $this->responseBody = $fetchVendor; 
            }
            else {
                $this->responseBody = false; 
            }
        }
        catch(Exception $e) {
           $this->responseBody = $this->sendError("Error", "Error processing request", 500); 
        }
        return $this->responseBody;
    }
    
}
?>
<?php
namespace App\Services;

use Exception;
use App\Models\Plans;
use App\Http\Traits\ResponseTrait;

class PlanService {
    use ResponseTrait;
    
    protected $responseBody;

    public function getAllPlans() {
        try {
            $plansDetail = Plans::orderBy("plan_name", "asc")->all();
            $this->responseBody = $this->sendResponse("Success", $plansDetail);
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error fetching plan ".$e->getMessage(), 400);
        }
        return $this->responseBody;
    }

    public function getPlan($planId) {
        try {
            $getPlan = Plans::where("id", $planId)->first();
            if($getPlan != NULL) {
                $this->responseBody = $getPlan;
            } 
            else {
                $this->responseBody = $this->sendError("Error", "Error! Unknown Plan ID", 400);
            }
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error fetching plan ".$e->getMessage(), 400);
        }
        return $this->responseBody;
    }
    
    public function deletePlan($planId) {
        try {
            $deletePlan = Plans::where("id", $planId)->delete();
            if($deletePlan) {
                $this->responseBody = $this->sendResponse("Deleted Successfully", $deletePlan, 204);
            } 
            else {
                $this->responseBody = $this->sendError("Error", "Error! Unknown Plan ID", 500);
            }
        }
        catch(Exception $e){
            $this->responseBody = $this->sendError("Error", "Error processing requesting", 500);
        } 
        return $this->responseBody;
    }
    
}
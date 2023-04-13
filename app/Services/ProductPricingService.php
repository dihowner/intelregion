<?php
namespace App\Services;

use Exception;
use App\Http\Traits\ResponseTrait;
use App\Models\ProductPricing;

class ProductPricingService {
    use ResponseTrait;
    protected $responseBody, $utilityServe;

    public function __construct(UtilityService $utilityServe) {
        $this->utilityServe = $utilityServe;
    }
    
    public function getPlanProductByProductCode(array $productInfo) {
        try {
            $product_id = $productInfo['product_id'];
            $planId = $productInfo['planId'];
            
            $getProductPricing = ProductPricing::where([
                "product_id" => $product_id,
                "plan_id" => $planId,
            ])->first();
            
            if($getProductPricing == NULL) {
                $this->responseBody = false;
            }
            else {
                $this->responseBody = $getProductPricing;
            }
            
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error processing request", 500);
        }
        return $this->responseBody;
    }
    
}
?>
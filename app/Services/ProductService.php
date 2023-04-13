<?php
namespace App\Services;

use Exception;
use App\Http\Traits\ResponseTrait;
use App\Models\Product;

class ProductService {

    use ResponseTrait;
    protected $responseBody, $utilityService;

    public function __construct(UtilityService $utilityService) {
        $this->utilityService = $utilityService;
    }
    
    public function getProduct(string $product_id) {
        try {
            $fetchProduct = Product::where("product_id", $product_id)->first();
            if($fetchProduct != NULL) {
                $this->responseBody = $fetchProduct; 
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
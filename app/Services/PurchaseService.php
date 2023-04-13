<?php
namespace App\Services;

use Exception;
use App\Models\User;
use App\Http\Traits\ResponseTrait;

// Import all vendors class
use App\Vendors\LocalServer;
use App\Vendors\MobileNig;

class PurchaseService {

    use ResponseTrait;
    protected $responseBody, $utilityService, $walletService, $productService, $productPriceService, $apiService;

    public function __construct(UtilityService $utilityService, WalletService $walletService, ProductService $productService,
                                ProductPricingService $productPriceService, ApiService $apiService) {
        $this->utilityService = $utilityService;
        $this->walletService = $walletService;
        $this->productService = $productService;
        $this->productPriceService = $productPriceService;
        $this->apiService = $apiService;
    }

    public function purchaseAirtime(array $airtimeData) {

        $network = $airtimeData['network'];
        $amount_topup = $airtimeData['amount'];
        $phone_number = $airtimeData['phone_number'];

        $theAuthorizedUser = auth()->user();
        $theUserId = $theAuthorizedUser->id;
        $theUserPlanId = $theAuthorizedUser->plan_id;

        $userBalance = (float) $this->walletService->getUserBalance($theUserId);


        $airtimeInfo = json_decode($this->utilityService->AirtimeInfo());

        $productInfo = $this->productService->getProduct($network);

        if($productInfo === false) {
            return $this->sendError("Error", "Error getting product ($network)", 400);
        }

        //  What's the cost price of the product
        $costPrice = (float) $amount_topup - (($amount_topup * $productInfo->cost_price)/100);
        //  Get product info based on plan...
        $getProductPlan = $this->productPriceService->getPlanProductByProductCode(['product_id'=>$network, 'planId' => $theUserPlanId]);

        if($getProductPlan === false) {
            return $this->sendError("Error", "Product plan pricing does not exists", 200);
        }

        if($amount_topup < $airtimeInfo->min_value) {
           return $this->sendError("Error", "Minimum airtime amount is N".$airtimeInfo->min_value, 200);
        }

        if($amount_topup > $airtimeInfo->max_value) {
            return $this->sendError("Error", "Maximum airtime amount is N".$airtimeInfo->max_value, 200);
        }

        // Which API is it connected to ? API ID ???
        $apiId = $productInfo->api_key; //Product API Id

        $this->responseBody = $theUserId . " ".$theUserPlanId . " ".$userBalance . " ".$apiId;

        $apiInfo = $this->apiService->getApi($apiId);

        if($apiInfo === false) {
            return $this->sendError("Error", "Access remote server not found", 404);
        }

        $vendor_code = $apiInfo->api_vendor->vendor_code;
        if($vendor_code == "not_available") {
            return $this->sendError("Error", "We are currently experiencing delivery degradation towards the ".strtoupper($network)." network for ".strtoupper($network)." Airtime traffic. Kindly try again in few minutes time", 403);
        }
        
        return $this->SendRequest($apiInfo->api_vendor->vendor_code, "", []);
        return $apiInfo->api_vendor->vendor_code;
        return $apiInfo;

        return $this->responseBody;
    }


    private function SendRequest($vendor_code, $category, array $payload) {
        switch($vendor_code) {
            case "local_server":
                $vendor = app(LocalServer::class);
                return $vendor->sendAirtime();
            break;
            
            case "mobilenig":

            break;
            default:
                return false;
        }
    }
    
}
?>
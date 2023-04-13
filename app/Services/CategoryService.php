<?php
namespace App\Services;

use Exception;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;

class CategoryService {

    use ResponseTrait;
    protected $responseBody, $utilityService;

    public function __construct(UtilityService $utilityService) {
        $this->utilityService = $utilityService;
    }

    public function listCategories()
    {
        try {
            $allCategories = Category::all();
            $this->responseBody = $this->sendResponse("Success", $allCategories);
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error fetching record", 500);
        }
        return $this->responseBody;
    }
    
}
?>
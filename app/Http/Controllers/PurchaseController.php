<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PurchaseService;
use App\Http\Requests\AirtimePurchaseRequest;

class PurchaseController extends Controller
{
    protected $purchaseService;
    
    // Purchase Airtime
    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function purchaseAirtime(AirtimePurchaseRequest $request) {
        return $this->purchaseService->purchaseAirtime($request->validated());
    }
    
}
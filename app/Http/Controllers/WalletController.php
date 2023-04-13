<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use App\Http\Requests\TransferFundRequest;
use App\Http\Requests\ManualFundingRequest;
use App\Http\Requests\WalletHistoryRequest;

class WalletController extends Controller
{
    protected $walletService;
    
    public function __construct(WalletService $walletService) {
        $this->walletService = $walletService;
    }

    public function createWalletRequest(ManualFundingRequest $request) {
        return $this->walletService->createWalletRequest($request->validated());
    }

    public function WalletHistory($id, WalletHistoryRequest $request) {
        return $this->walletService->WalletHistory($id, $request->validated());
    }

    public function TransferFund(TransferFundRequest $request) {
        return $this->walletService->TransferFund($request->validated());
    }
}
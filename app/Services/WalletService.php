<?php
namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\WalletIn;
use App\Models\WalletOut;
use App\Http\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

class WalletService {
    use ResponseTrait;
    
    protected $utilityService, $responseBody;
    public function __construct(UtilityService $utilityService) {
        $this->utilityService = $utilityService;
    }
    
    public function TransferFund(array $fundData) {
        try {
            $receiver_id = $fundData['user_id'];
            $amount = (float) $fundData['amount'];
            $theReceiver = User::where("id", $receiver_id)->first();
            
            $theSender = auth()->user();
            $theSenderBalance = (float) $this->getUserBalance($theSender->id);

            if($theReceiver == NULL) {
                $this->responseBody = $this->sendError("Error", "User does not exist", 400);
            }
            else {
                if($theReceiver->id == $theSender->id) {
                    $this->responseBody = $this->sendError("Error", "You cannot share fund with yourself", 400);
                }
                else if($amount > $theSenderBalance) {  
                    $this->responseBody = $this->sendError("Error", "Insufficient wallet balance, kindly top-up your wallet", 400);
                }
                else {
                    $receiverBalance = $this->getUserBalance($receiver_id);
                    $uniqueReference = $this->utilityService->uniqueReference();
                    $walletInData = [
                        "user_id" => $receiver_id,
                        "description" => "Fund Transfer",
                        "old_balance" => (float) $receiverBalance,
                        "amount" => $amount,
                        "new_balance" => (float) ($receiverBalance + $amount),
                        "reference" => $uniqueReference,
                        "status" => "1",
                        "remark" => json_encode(["created_by" => $theSender->fullname, "approved_by" => $theSender->fullname]) 
                    ];

                    $theSenderNewBalance = (float) $theSenderBalance - $amount;
                    
                    $walletOutData = [
                        "user_id" => $theSender->id,
                        "description" => "Fund Transfer",
                        "old_balance" => (float) $theSenderBalance,
                        "amount" => $amount,
                        "new_balance" => $theSenderNewBalance,
                        "reference" => $uniqueReference,
                        "status" => "1",
                        "remark" => json_encode(["created_by" => $theSender->fullname, "approved_by" => $theSender->fullname]) 
                    ];
                    
                    DB::beginTransaction();
                    try { 
                        $this->createWallet("inward", $walletInData);
                        $this->createWallet("outward", $walletOutData);
                        DB::commit();
                        
                        $responseData = [
                            "receiver" => [
                                "id" => $receiver_id,
                                "fullname" => User::where("id", $receiver_id)->first()->fullname,
                            ],
                            "old_balance" => $theSenderBalance,
                            "amount" => $amount,
                            "new_balance" => $theSenderNewBalance
                        ];
                        $this->responseBody = $this->sendResponse("Wallet transferred successfully", $responseData);
                    }
                    catch(Exception $e) {
                        DB::rollBack();
                        $this->responseBody = $this->sendError("Error", "Unable to complete request ".$e->getMessage(), 400);
                    }
                }
            }
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error transferring fund ".$e->getMessage(), 400);
        }
        return $this->responseBody;
    }

    public function WalletHistory($userId, $pageLimit)
    {  
        try {
            $perPageLimit = ($pageLimit['limit'] > 10 ? 10 : $pageLimit['limit']);
            if($userId == "") {
                $walletIn = WalletIn::orderBy('created_at', 'desc')->take(10)->paginate($perPageLimit);
                $walletOut = WalletOut::orderBy('created_at', 'desc')->take(10)->paginate($perPageLimit);
            } else {
                $walletIn = WalletIn::where("user_id", $userId)->orderBy('created_at', 'desc')->take(10)->paginate($perPageLimit);
                $walletOut = WalletOut::where("user_id", $userId)->orderBy('created_at', 'desc')->take(10)->paginate($perPageLimit);
            }
            
            $histories = $walletIn->concat($walletOut);
            $histories = $histories->sortByDesc('created_at');
            $this->responseBody = $histories;
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error fetching records ".$e->getMessage(), 400);
        }
        return $this->responseBody;
    }

    public function CreateWalletRequest(array $fundingData) {
        try {
            $amount = (float) $fundingData['amount'];
            $theAuthorizedUser = auth()->user();
            $walletReference = $this->utilityService->uniqueReference();
            $data = [
                "user_id" => $theAuthorizedUser->id,
                "description" => "Manual wallet funding request",
                "reference" => $walletReference,
                "amount" => $amount          
            ];
            $this->createWallet("inward", $data);
            return $this->sendResponse("Wallet funding request created successfully", ["reference" => $walletReference, "amount" => $amount]);
        }
        catch(Exception $e) {
            return "Error: ".$e->getMessage();
        }
    }
    
    //Wallet In Method
    private function sumWalletIn($userId = '') {
        if($userId == "") {
            $sumWallet = WalletIn::where(['status' => '1'])->sum('amount');
        } else {
            $sumWallet = WalletIn::where(['user_id' => $userId, 'status' => '1'])->sum('amount');
        }
        return $sumWallet;
    }

    //Wallet Out Method
    private function sumWalletOut($userId = '') {
        if($userId == "") {
            $sumWallet = WalletOut::where(['status' => '1'])->sum('amount');
        } else {
            $sumWallet = WalletOut::where(['user_id' => $userId, 'status' => '1'])->sum('amount');
        }
        return $sumWallet;
    }

    public function getUserBalance($userId) {
        $balanceLeft = $this->sumWalletIn($userId) - $this->sumWalletOut($userId);
        if($balanceLeft <= 0) {
            $balanceLeft = (float) (0);
        } else {
            $balanceLeft = (float) $balanceLeft;
        }
        return $balanceLeft;
    }
    
    public function createWallet(string $walletType, array $data) {
        switch($walletType) {
            case "inward":
                return WalletIn::create($data);
            break;
            
            case "outward":
                return WalletOut::create($data);
            break;
        }
    }
    
}
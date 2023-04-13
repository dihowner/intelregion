<?php
namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\UserMeta;
use App\Http\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService {
    use ResponseTrait;
    protected $responseBody, $monnifyService, $utilityService, $walletService, $planService;

    public function __construct(MonnifyService $monnifyService, UtilityService $utilityService, WalletService $walletService, PlanService $planService) {
        $this->utilityService = $utilityService;
        $this->monnifyService = $monnifyService;
        $this->walletService = $walletService;
        $this->planService = $planService;
    }
    
    public function UpgradePlan(array $planData) {
        try {
            $theAuthorizedUser = $this->getUserById(auth()->user()->id);

            // Get user wallet balance...
            $currentBalance = $this->walletService->getUserBalance($theAuthorizedUser->id);

            $new_plan_id = $planData['new_plan_id'];

            if($theAuthorizedUser->plan->id == $new_plan_id) {
                $this->responseBody = $this->sendError("Error", "You cannot upgrade to your current plan", 400);
            }
            else {
                $planDetail = $this->planService->getPlan($new_plan_id);
                $planAmount = (float) $planDetail->upgrade_amount;
                if($planAmount > $currentBalance) {
                    $this->responseBody = $this->sendError("Error", "Your wallet balance (".number_format($currentBalance, 2).") is not sufficient to process your request", 400);
                }
                else {
                    
                    $walletOutData = [
                        "user_id" => $theAuthorizedUser->id,
                        "description" => "Plan upgrade from ".$theAuthorizedUser->plan->plan_name . " to ". $planDetail->plan_name,
                        "old_balance" => $currentBalance,
                        "amount" => $planAmount,
                        "new_balance" => (float) ($currentBalance - $planAmount),
                        "status" => "1",
                        "reference" => $this->utilityService->uniqueReference(),
                        "remark" => json_encode(["created_by" => $theAuthorizedUser->fullname, "approved_by" => "System"])
                    ];
                    
                    DB::beginTransaction();
                    try {
                        $this->walletService->createWallet("outward", $walletOutData);
                        User::where("id", $theAuthorizedUser->id)->update(["plan_id" => $new_plan_id]);
                        DB::commit();
                        $this->responseBody = $this->sendResponse("Plan Upgrade was successful", $this->getUserById(auth()->user()->id));
                    } 
                    catch(Exception $e) {
                        DB::rollBack();
                        $this->responseBody = $this->sendError("Error", "Error updating plan ".$e->getMessage(), 400);
                    }  
                }                    
            }
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error updating password ".$e->getMessage(), 400);
        }
        return $this->responseBody;
    }

    public function ModifyUserPassword(array $passwordData) {
        try {
            $user = Auth::user();
            
            User::where("id", $user->id)->update([
                'password' => Hash::make($passwordData['new_password'])
            ]);
            
            $this->responseBody = $this->sendResponse("Updated Successfully", $user);
        }
        catch(Exception $e) {
            $this->responseBody = $this->sendError("Error", "Error updating password ".$e->getMessage(), 400);
        }
        return $this->responseBody;
    }

    public function GenerateUserVirtualAccount() {
        try {
            $theAuthorizedUser = $this->getUserById(auth()->user()->id);
            $reservedReference = $this->utilityService->uniqueReference();

            $generateVirtualAccount = $this->monnifyService->generateVirtualAccount([
                "user_id" => $theAuthorizedUser->id,
                "username" => $theAuthorizedUser->username, 
                "email_address" => $theAuthorizedUser->emailaddress, 
                "reference" => $reservedReference
            ]);
            
            if(!is_array($generateVirtualAccount)) {
                $decodeResult = json_decode($generateVirtualAccount);
                $this->responseBody = $this->sendError("Error", $decodeResult->message, 400);
            } else {
                // Since virtual account is generated, then we need to update the old record from DB...
                if(isset($theAuthorizedUser->user_meta["monnify"])) {

                    // Remove it from monnify server...
                    $this->monnifyService->deleteReservedAccount($theAuthorizedUser->auto_funding_reference);    
                    
                    DB::beginTransaction();
                    try {
                        // Update user table...
                        User::where("id", $theAuthorizedUser->id)->update(["auto_funding_reference" => $reservedReference]);
                        
                        // Update User Meta table...
                        UserMeta::where([
                            "user_id" => $theAuthorizedUser->id,
                            "name" => "monnify",
                        ])->update(["value" => json_encode($generateVirtualAccount)]);

                        DB::commit();
                        $this->responseBody = $this->sendResponse("Virtual account updated successfully", $generateVirtualAccount);
                    }
                    catch(Exception $e) {
                        DB::rollback();
                        $this->responseBody = $this->sendError("Error", $e->getMessage(), 400);
                    }
                } 
                else {
                    DB::beginTransaction();
                    try {
                        // Create the user meta instance...
                        $userMetaData = [
                            "user_id" => $theAuthorizedUser->id,
                            "name" => "monnify",
                            "value" => json_encode($generateVirtualAccount),
                            "date_created" => $this->utilityService->dateCreated()
                        ];
                        $this->createUserMeta($userMetaData);
                        
                        // Update user table...
                        User::where("id", $theAuthorizedUser->id)->update(["auto_funding_reference" => $reservedReference]);
                        DB::commit();
                        $this->responseBody = $this->sendResponse("Virtual account generated successfully", $generateVirtualAccount);
                    }
                    catch(Exception $e) {
                        DB::rollback();
                        $this->responseBody = $this->sendError("Error", $e->getMessage(), 400);
                    }
                }
            }
            return $this->responseBody;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }        
    } 

    public function getUserById($userId) {
        try {            
            $theUser = User::where("id", $userId)->first();
            if($theUser != NULL) {
                $theUser['plan'] = $this->planService->getPlan($theUser['plan_id']);
                $theUser['wallet_balance'] = (float) $this->walletService->getUserBalance($theUser->id);
                $userMeta = $this->getUserMeta($userId);
                if($userMeta !== false) {
                    $theUser['user_meta'] = $userMeta;
                }
                unset($theUser['plan_id']);
                $this->responseBody = $theUser;
            } else {
                $this->responseBody = $this->sendError("User could not be found", [], 400);
            }
            return $this->responseBody;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }
    
    private function getUserMeta($userId) {
        
        $userMeta = UserMeta::where("user_id", $userId)->get();

        if(count($userMeta) > 0) {
            
            foreach($userMeta as $index => $value) {
                $newMeta[$userMeta[$index]['name']] = $userMeta[$index]['value'];
            }

            if(isset($newMeta['monnify'])) {
                $monnify = new MonnifyService($this->utilityService);
                $userMonnify = json_decode($newMeta['monnify']);
                $index = 0;
                foreach($userMonnify as $bankCode => $accountNo) {
                    $newUserMonnify[$monnify->getVirtualBankByCode($bankCode)->bank_name] = $accountNo;
                }
                $newMeta['monnify'] = $newUserMonnify;
            }
            $this->responseBody  = $newMeta;
        }
        else {
            $this->responseBody = false;
        }
        return $this->responseBody;
    } 

    private function createUserMeta(array $userMetaData) {
        try {
            return UserMeta::create($userMetaData);
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

}
?>
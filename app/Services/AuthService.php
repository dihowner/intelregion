<?php
namespace App\Services;

use Exception;
use App\Models\User;
use App\Http\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService {

    use ResponseTrait;
    protected $responseBody, $utilityService;

    public function __construct(UtilityService $utilityService) {
        $this->utilityService = $utilityService;
    }

    // Account creation method....
    public function createAccount(array $data) {
        try {
            $default_plan_id = $this->utilityService->defaultPlanId();
            
            if($default_plan_id == NULL OR !is_numeric($default_plan_id)) {
                $this->responseBody = $this->sendError('Error', "Default system plan not found", 400);
            }
            else {
                $data['password'] = Hash::make($data['password']);
                $data['plan_id'] = $default_plan_id;
                $theUser = User::create($data);
                $this->responseBody = $this->sendResponse("User account created successfully", $theUser);
            }
        }
        catch(Exception $e) {
            Log::error($e->getMessage());
            $this->responseBody = $this->sendError('Request Failed', "Error creating user account", 500);
        }
        return $this->responseBody;
    }

    public function loginAccount(array $loginData) {
        try {
            $checkUser = User::where([
                "username" => $loginData['user_detail']
            ])->orWhere([
                "emailaddress" => $loginData['user_detail']
            ])->orWhere([
                "phone_number" => $loginData['user_detail']
            ])->first();

            if(!$checkUser) {
                $this->responseBody = $this->sendError("Failed", "Bad combination of username or password", 400);
            }
            else {
                if(Auth::attempt(['username' => $checkUser->username, 'password' => $loginData['password']])) {
                    $user = Auth::user();
                    $userData = [
                        "id" => $user->id,
                        "username" => $user->username,
                        "fullname" => $user->fullname,
                        "token" => $user->createToken($checkUser->username)->plainTextToken
                    ];
                    $this->responseBody = $this->sendResponse("Login ssuccessful", $userData);
                }
                else {
                    $this->responseBody = $this->sendError("Failed", "Unauthorized Access", 400);
                }
            }
        }
        catch(Exception $e) {
            Log::error($e->getMessage());
            $this->responseBody = $this->sendError('Request Failed', "Unexpected error occurred", 500);
        }
        return $this->responseBody;
    }
}
?>
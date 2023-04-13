<?php
namespace App\Services;

use Exception;
use App\Models\VirtualBank;
use App\Http\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class MonnifyService extends UserService {
    use ResponseTrait;
    protected $responseBody, $utilityService, $userService;

    private $v1 = "/api/v1/";
    private $v2 = "/api/v2/";
    private $monnifyInfo, $endpoint, $apiKey, $secKey, $contractCode, $charges, $chargestype;

    public function __construct(UtilityService $utilityService)
    {
        $this->utilityService = $utilityService;
        $this->monnifyInfo = json_decode($this->utilityService->monnifyInfo());
        $this->endpoint = $this->monnifyInfo->baseUrl;
        $this->apiKey = $this->monnifyInfo->apiKey;
        $this->secKey = $this->monnifyInfo->secKey;
        $this->contractCode = $this->monnifyInfo->contractCode;
        $this->charges = (float) $this->monnifyInfo->charges;
        $this->chargestype = $this->monnifyInfo->chargestype;
    }

    public function getAllVirtualBanks() {
        return VirtualBank::all();
    }

    public function getVirtualBankByCode($bank_code) {
        $getBank = VirtualBank::where("bank_code", $bank_code)->first();
        if($getBank != NULL) {
            $result = $getBank;
        }
        else {
            $result = false;
        }
        return $result;
    }

    private function generateAuthToken() {
        try {

            $result = Http::withHeaders([
                "Authorization" => "Basic ".base64_encode($this->apiKey.':'.$this->secKey),
                "Content-Type" => "application/json"
            ])->post($this->endpoint.$this->v1."auth/login");

            $accessToken = json_decode($result);
            $bearerToken = isset($accessToken->responseBody->accessToken) ? $accessToken->responseBody->accessToken : false;
            $this->responseBody = $bearerToken;
        } catch (RequestException $e) {
            // Handle request exceptions (e.g. 4xx, 5xx status codes)
            $this->responseBody = [
                "status_code" => $e->getCode(),
                "message" => $e->getMessage()
            ];
        } catch (\Exception $e) {
            // Handle other exceptions (e.g. network errors)
            $this->responseBody = ["message" => $e->getMessage()];
        }
        return $this->responseBody;
    }

    public function generateVirtualAccount(array $userData) {
        try {
            $allVirtualBank = $this->getAllVirtualBanks();
            if($allVirtualBank === false) {
                return $this->sendError('Error', "Virtual Bank(s) could not be retrieved", 404);
            }

            for($i = 0; $i < count($allVirtualBank); $i++) {
                $bank_code[] = $allVirtualBank[$i]['bank_code'];
            }

            $reserveBody = [
                "accountReference" => $userData['reference'],
                "accountName" => $userData['username'],
                "currencyCode" => "NGN",
                "contractCode" => $this->contractCode,
                "customerEmail" => $userData['email_address'],
                "customerName" => $userData['username'],
                "getAllAvailableBanks" => false,
                "preferredBanks" => $bank_code
            ];

            $reserveResult = Http::withHeaders([
                "Authorization" => "Bearer ".$this->generateAuthToken(),
                "Content-Type" => "application/json"
            ])->post($this->endpoint.$this->v2."bank-transfer/reserved-accounts", $reserveBody);

            $decodeReserve = json_decode($reserveResult, true);

            $getAccount = isset($decodeReserve['responseBody']['accounts']) ? $decodeReserve['responseBody']['accounts'] : false;
            if($getAccount !== false) {

                for($i = 0; $i < count($getAccount); $i++) {
                    $bank_code = $getAccount[$i]['bankCode'];
                    $accountNumber = $getAccount[$i]['accountNumber'];
                    $newArray[$bank_code] = $accountNumber;
                }
                $this->responseBody = $newArray;
            }
            else {
                $this->responseBody = $this->sendError('Request Failed', "Error reserving account", 400);
            }
        }
        catch(Exception $e) {
            Log::error($e->getMessage());
            $this->responseBody = $this->sendError('Request Failed', "System Error!", 400);
        }
        return $this->responseBody;
    }

    public function deleteReservedAccount($accountReference) {
        try {
            $deleteAccount = Http::withHeaders([
                "Authorization" => "Bearer ".$this->generateAuthToken(),
                "Content-Type" => "application/json"
            ])->delete($this->endpoint.$this->v1."bank-transfer/reserved-accounts/reference/".$accountReference);

            $decodeDelete = json_decode($deleteAccount);

            if(isset($decodeDelete->responseCode) AND $decodeDelete->responseCode != 200) {
                $this->responseBody = $this->sendError("Error", $decodeDelete->responseMessage, 200);
            }
            else if(isset($decodeDelete->responseBody) AND $decodeDelete->responseBody->accountReference == $accountReference) {
                $this->responseBody = $this->sendResponse("Success", "Virtual Account ({$accountReference}) deallocated successfully", 200);
            }
            else {
                $this->responseBody = $this->sendError("Error", "Error deallocating virtual account", 400);
            }
        }
        catch (RequestException $e) {
            // Handle request exceptions (e.g. 4xx, 5xx status codes)
            $this->responseBody = [
                "status_code" => $e->getCode(),
                "message" => $e->getMessage()
            ];
        } catch (\Exception $e) {
            // Handle other exceptions (e.g. network errors)
            $this->responseBody = ["message" => $e->getMessage()];
        }
        return $this->responseBody;
    }

}
?>
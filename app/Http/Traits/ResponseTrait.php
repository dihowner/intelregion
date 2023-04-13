<?php
namespace App\Http\Traits;

/**
 * 
 */
trait ResponseTrait
{
    public function sendResponse($message, $result, $status = 200) {

    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, $status);
    }


    /**
     * return error response. *

     * @return \Illuminate\Http\Response
    */

    public function sendError($error, $errorMessages = [], $code = 404) {
    	$response = [
            'success' => false,
            'message' => $error,
        ];
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}

?>
<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AirtimePurchaseRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            "amount" => "integer|required|min:10|max:5000000",
            "phone_number" => "string|required|min:11|max:11",
            "network" => "string|required"
        ];
    }

    public function messages()
    {
        return [
            "amount.integer" => "Airtime amount field must be a whole number and not a decimal number",
            "amount.required" => "Airtime amount field is required",
            "phone_number.required" => "Airtime amount field is required",
            "network.required" => "Network MSIDN field is required",
            "amount.min" => "Minimum Airtime amount is N10",
            "amount.max" => "Maximum Airtime amount is N5000000"
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError("Validation Failed", $validator->errors(), 422));
    }
}
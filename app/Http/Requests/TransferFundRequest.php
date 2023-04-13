<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransferFundRequest extends FormRequest
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
            "amount" => "numeric|required|digits_between:1,10000|min:50|max:10000",
            "user_id" => "numeric|required"
        ];
    }

    public function messages()
    {
        return [
            "amount.required" => "Amount is required",
            "amount.numeric" => "Amount must be a numeric value",
            "amount.digits_between" => "Amount  must be between :min and :max digits" ,
            "amount.min" => "Minimum transferrable amount is N50"  ,
            "amount.max" => "Maximum transferrable amount is N10000"  
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError("Validation Failed", $validator->errors(), 422));
    }
}
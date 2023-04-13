<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpgradePlanRequest extends FormRequest
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
            "new_plan_id" => "required|numeric"
        ];
    }
    
    public function messages()
    {
        return [
            "new_plan_id.required" => "New Plan ID is required",
            "new_plan_id.numeric" => "Plan ID must be a numeric value"
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError("Validation Failed", $validator->errors(), 422));
    }
}
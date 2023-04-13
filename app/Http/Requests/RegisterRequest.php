<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            "username" => "required|unique:users|string|min:3",
            "fullname" => "required|string",
            "phone_number" => "required|unique:users|digits_between:11,11",
            "emailaddress" => "required|unique:users|email",
            "password" => "required"
        ];
    }

    public function messages()
    {
        return [
            "username.required" => "Username is required",
            "username.unique" => "Username ({$this->username}) already belongs to a user",
            "phone_number.unique" => "Phone number ({$this->phone_number}) already belongs to a user",
            "emailaddress.unique" => "Email address ({$this->emailaddress}) already belongs to a user"
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError("Validation Failed", $validator->errors(), 422));
    }
}
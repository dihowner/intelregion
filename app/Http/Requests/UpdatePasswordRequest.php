<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePasswordRequest extends FormRequest
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
            "old_password" => "required|min:5|string",
            "new_password" => "required|min:5|string|confirmed",
            "new_password_confirmation" => "required|min:5|string"
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = Auth::user();

            if (! Hash::check($this->input('old_password'), $user->password)) {
                $validator->errors()->add('old_password', 'The provided password does not match your current password.');
            }
        });
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError("Validation Failed", $validator->errors(), 422));
    }

}
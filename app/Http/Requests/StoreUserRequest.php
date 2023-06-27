<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'name_local' => 'required|string|max:255',
//            'national_id' => 'required|string',
            // 'type' => 'required',

            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->user)->where(function ($query) {
                $query->where(['removed' => 0]);
            })]
        ];

        return $rules;
    }
}

<?php

namespace App\Requests;

use Core\Support\Validation\Validator;

class NewRequest extends Validator
{
    public function rules()
    {
        return [
            'a' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'a.required' => 'required'
        ];
    }
}

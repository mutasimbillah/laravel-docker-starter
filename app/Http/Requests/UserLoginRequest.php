<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest {
    public function authorize() {
        return true;
    }

    public function rules() {
        return array(
            'phone' => 'required|string|min:11|max:11',
        );
    }
}

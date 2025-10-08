<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        $userId = $this->route('id') ?? null; // to allow unique email on update

        return [
            'name'     => 'required|string|min:3|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $userId,
            'password' => $this->isMethod('post') 
                            ? 'required|string|min:8|confirmed'
                            : 'nullable|string|min:8|confirmed',
        ];
    }

    public function attributes()
    {
        return [
            'name'     => 'Full Name',
            'email'    => 'Email Address',
            'password' => 'Password',
        ];
    }

    public function messages()
    {
        return [
            'name.required'  => 'The user name is required.',
            'email.required' => 'An email address is required.',
            'email.unique'   => 'This email is already taken.',
            'password.required' => 'A password is required when creating a new user.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}

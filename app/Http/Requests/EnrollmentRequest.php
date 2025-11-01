<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'user_id'   => 'required|exists:users,id',
        ];
    }

    public function attributes()
    {
        return [
            'course_id' => 'Course',
            'user_id'   => 'User',
        ];
    }

    public function messages()
    {
        return [
            'course_id.required' => 'Please select a course.',
            'user_id.required'   => 'Please select a user.',
        ];
    }
}

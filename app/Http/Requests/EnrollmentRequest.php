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
            'course_id'     => 'required|exists:courses,id',
            'student_name'  => 'required|string|min:3|max:255',
            'student_email' => 'required|email|max:255',
            'phone'         => 'nullable|string|max:20',
        ];
    }

    public function attributes()
    {
        return [
            'course_id'     => 'Course',
            'student_name'  => 'Student Name',
            'student_email' => 'Student Email',
            'phone'         => 'Phone Number',
        ];
    }

    public function messages()
    {
        return [
            'course_id.required' => 'Please select a course.',
            'student_name.required' => 'Student name is required.',
            'student_email.required' => 'Student email is required.',
        ];
    }
}

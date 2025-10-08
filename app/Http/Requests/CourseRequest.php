<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'title'       => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:5000',
            'author_id'   => 'required|exists:users,id',
        ];
    }

    public function attributes()
    {
        return [
            'title'       => 'Course Title',
            'description' => 'Course Description',
            'author_id'   => 'Author',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The course title is required.',
            'author_id.required' => 'Please select an author.',
        ];
    }
}

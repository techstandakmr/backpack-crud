<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'title'     => 'required|string|min:3|max:255',
            'content'   => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
        ];
    }

    public function attributes()
    {
        return [
            'title'     => 'Lesson Title',
            'content'   => 'Lesson Content',
            'course_id' => 'Course',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Lesson title is required.',
            'course_id.required' => 'Please select a course.',
        ];
    }
}

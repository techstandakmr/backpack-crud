<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'lesson_id' => 'required|exists:lessons,id',
            'name'      => 'required|string|min:3|max:255',
            'url'       => 'required|url|max:500',
        ];
    }

    public function attributes()
    {
        return [
            'lesson_id' => 'Lesson',
            'name'      => 'Resource Name',
            'url'       => 'Resource URL',
        ];
    }

    public function messages()
    {
        return [
            'lesson_id.required' => 'Please select a lesson.',
            'name.required' => 'Resource name is required.',
            'url.required' => 'Resource URL is required.',
            'url.url' => 'Please provide a valid URL.',
        ];
    }
}

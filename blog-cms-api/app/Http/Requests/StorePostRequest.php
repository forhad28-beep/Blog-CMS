<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published',

            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',

            'featured_image' =>
                'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'category_id.required' => 'Please select a category.',
            'featured_image.image' => 'Please upload a valid image.',
        ];
    }
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'featured_image' => 'featured image',
        ];
    }
}
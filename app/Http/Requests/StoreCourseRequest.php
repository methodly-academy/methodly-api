<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'type' => 'required|in:free,premium',
            'price' => 'required_if:type,premium|numeric|min:0',
            'level_id' => 'required|exists:levels,id',
            'is_published' => 'boolean',
        ];
    }
}

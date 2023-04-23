<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImagesSyncRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'product_url' => 'required|url|regex:/^https:\/\/www\.honeysplace\.com\/product\//',
            'product_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'product_url.regex' => 'The product url must start with: https://www.honeysplace.com/product/ '
        ];
    }
}

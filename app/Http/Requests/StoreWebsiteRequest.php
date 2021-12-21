<?php

namespace App\Http\Requests;

use App\Rules\ValidateWebsiteURLRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebsiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'url' => ['required', 'url', 'max:255', new ValidateWebsiteURLRule()],
            'privacy' => ['nullable', 'integer', 'between:0,2'],
            'password' => [Rule::requiredIf($this->input('privacy') == 2), 'nullable', 'string', 'min:1', 'max:128'],
            'exclude_bots' => ['nullable', 'integer', 'between:0,1'],
            'exclude_params' => ['nullable', 'string'],
            'exclude_ips' => ['nullable', 'string'],
            'email' => ['nullable', 'integer']
        ];
    }
}

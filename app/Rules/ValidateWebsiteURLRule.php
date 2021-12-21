<?php

namespace App\Rules;

use App\Website;
use Illuminate\Contracts\Validation\Rule;

class ValidateWebsiteURLRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $url = parse_url(str_replace('://www.', '://', $value))['host'];
        } catch (\Exception $e) {
            return false;
        }

        if (Website::where('url', '=', $url)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('This domain is already being used.');
    }
}

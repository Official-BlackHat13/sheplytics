<?php

namespace App\Rules;

use App\Website;
use Illuminate\Contracts\Validation\Rule;

class ValidateWebsitePasswordRule implements Rule
{
    /**
     * @var
     */
    private $website;

    /**
     * Create a new rule instance.
     *
     * @param $website
     * @return void
     */
    public function __construct($website)
    {
        $this->website = $website;
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
        if ($this->website->password == $value) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The entered password is not correct.');
    }
}

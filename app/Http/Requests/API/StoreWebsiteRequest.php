<?php

namespace App\Http\Requests\API;

use App\Http\Requests\StoreWebsiteRequest as BaseCreateWebsiteRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreWebsiteRequest extends BaseCreateWebsiteRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => $validator->errors(),
                'status' => 422
            ], 422));
    }
}


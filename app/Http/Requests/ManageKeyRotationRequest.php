<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageKeyRotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => 'required|string',
            'action' => 'required|string|in:add_key,release_key,revoke_key,test_rotation,set_cooldown',
            'api_key' => 'required_if:action,add_key|nullable|string',
            'key_name' => 'nullable|string|max:255',
            'key_id' => 'required_if:action,release_key,revoke_key,set_cooldown|nullable|string',
            'cooldown_minutes' => 'nullable|integer|min:1|max:10080',
        ];
    }
}

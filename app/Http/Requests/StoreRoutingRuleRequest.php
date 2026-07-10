<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoutingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'uuid'],
            'intent_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('intent_routing', 'intent_name')->ignore($this->input('id')),
            ],
            'default_provider_id' => ['nullable', 'uuid', 'exists:ai_providers,id'],
            'default_model_id' => ['nullable', 'uuid', 'exists:ai_models,id'],
            'fallback_provider_id' => ['nullable', 'uuid', 'exists:ai_providers,id'],
            'fallback_model_id' => ['nullable', 'uuid', 'exists:ai_models,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAgentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agent_id' => 'required|uuid',
            'name' => 'required|string|max:255',
            'system_prompt' => 'nullable|string',
            'temperature' => 'required|numeric|between:0,2',
            'max_tokens' => 'required|integer|min:100|max:32000',
            'primary_model_id' => 'nullable|string',
            'fallback_models' => 'nullable|array|max:3',
            'fallback_models.*' => 'nullable|string',
            'tools' => 'nullable|array',
            'skills' => 'nullable|array',
        ];
    }
}

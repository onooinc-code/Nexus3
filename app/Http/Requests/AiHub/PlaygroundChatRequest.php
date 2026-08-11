<?php

declare(strict_types=1);

namespace App\Http\Requests\AiHub;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaygroundChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'string', 'exists:ai_providers,id'],
            'model_id' => [
                'required',
                'string',
                Rule::exists('ai_models', 'id')->where(function ($query) {
                    $query->where('provider_id', $this->input('provider_id'));
                })->or(function ($query) {
                    $query->where('id', $this->input('model_id'));
                }),
            ],
            'message' => ['required', 'string'],
        ];
    }
}

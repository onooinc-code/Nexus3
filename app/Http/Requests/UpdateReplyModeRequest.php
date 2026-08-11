<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReplyModeRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     *
     * @phpstan-ignore-next-line
     */
    public function rules(): array
    {
        return [
            'reply_mode' => ['required', 'string', 'in:manual,auto,hybrid,ai_only,assisted,autopilot,copilot,disabled'],
        ];
    }
}

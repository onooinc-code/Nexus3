<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'unique:ai_providers,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['nullable', 'url', function ($attribute, $value, $fail) {
                if (! $value) {
                    return;
                }
                $host = parse_url($value, PHP_URL_HOST);
                if (! $host) {
                    return $fail("The $attribute is invalid.");
                }
                if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'])) {
                    return $fail("The $attribute cannot be a local address.");
                }
                $ip = gethostbyname($host);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                    return $fail("The $attribute resolves to a private IP or is unresolvable.");
                }
            }],
            'models_fetch_endpoint' => ['nullable', 'string'],
            'generate_endpoint' => ['nullable', 'string'],
            'test_endpoint' => ['nullable', 'string'],
            'auth_header_format' => ['nullable', 'string'],
            'payload_format' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'api_key' => ['nullable', 'string'],
        ];
    }
}

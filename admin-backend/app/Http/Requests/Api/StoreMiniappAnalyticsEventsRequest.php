<?php

namespace App\Http\Requests\Api;

use App\Support\MiniappAnalyticsEventCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMiniappAnalyticsEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_session_id' => ['nullable', 'string', 'max:64'],
            'events' => ['required', 'array', 'min:1', 'max:20'],
            'events.*.event_name' => ['required', 'string', Rule::in(MiniappAnalyticsEventCatalog::eventNames())],
            'events.*.event_value' => ['nullable', 'string', 'max:255'],
            'events.*.meta' => ['nullable', 'array'],
        ];
    }
}

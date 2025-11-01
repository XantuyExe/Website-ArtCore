<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReturnConfirmRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()?->is_admin ?? false; }
    public function rules(): array {
        return [
            'action'        => ['required', Rule::in(['invoice','finalize'])],
            'cleaning_fee' => ['nullable','integer','min:0'],
            'damage_fee'   => ['nullable','integer','min:0'],
            'condition_note' => ['nullable','string','max:2000'],
        ];
    }
}

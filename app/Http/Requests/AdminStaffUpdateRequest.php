<?php

namespace App\Http\Requests;

use App\Enums\StaffRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminStaffUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore(
                    $this->route('staff')?->user_id
                ),
            ],
            'role' => ['required', Rule::enum(StaffRole::class)],
        ];
    }
}

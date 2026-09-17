<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ReservationStatus;

class StaffReservationUpdateRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'staff_id' => ['required', 'exists:staffs,id'],
      'menu_id' => ['required', 'exists:menus,id'],
      'start_at' => ['required', 'date'],
      'status' => ['required', Rule::enum(ReservationStatus::class)],
    ];
  }
}

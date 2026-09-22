<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffMenuUpdateRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'staff_id' => ['required', 'integer', 'exists:staffs,id'],
      'menu_ids' => ['array'],
      'menu_ids.*' => ['integer', 'exists:menus,id'],
    ];
  }
}

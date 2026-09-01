<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessHourRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'open_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'close_time' => [
                'nullable',
                'date_format:H:i',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // filled() は null や ""（空文字）を false と判定してくれる
            $hasOpenTime = filled($this->input('open_time'));
            $hasCloseTime = filled($this->input('close_time'));

            // open_time だけ入力されていて、close_time が空の場合
            if ($hasOpenTime && ! $hasCloseTime) {
                $validator->errors()->add(
                    'close_time',
                    'open_timeを入力する場合はclose_timeも入力してください。'
                );
            }

            // close_time だけ入力されていて、open_time が空の場合
            if (! $hasOpenTime && $hasCloseTime) {
                $validator->errors()->add(
                    'open_time',
                    'close_timeを入力する場合はopen_timeも入力してください。'
                );
            }
        });
    }
}

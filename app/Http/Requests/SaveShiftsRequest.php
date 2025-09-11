<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use App\Rules\EndTimeAfterStartTime;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaveShiftsRequest extends FormRequest
{
   
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        
        $rules = [
                'user_ids' => 'required|array',
                'shifts' => 'array',
        ];

        foreach ($this->input('shifts', []) as $day => $dayShifts) {
            foreach ($dayShifts as $index => $shift) {
                $startPath = "shifts.$day.$index.start_time";
                $endPath = "shifts.$day.$index.end_time";

                $rules[$startPath] = ['required'];
                $rules[$endPath] = [
                    'required',
                    new EndTimeAfterStartTime($shift['start_time'] ?? null)
                ];
            }
        }

    return $rules;
        
    }
    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        // Filter dynamically for all end_time errors
        $firstShiftError = collect($errors->getMessages())
            ->filter(function ($messages, $key) {
                return Str::startsWith($key, 'shifts.') && Str::contains($key, 'end_time');
            })
            ->flatten()
            ->first();
        throw new HttpResponseException(response()->json([
            'success' => false,
            'msg' => $firstShiftError,

        ]));
    }
}

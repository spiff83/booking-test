<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'variant_id' => ['required','integer','exists:service_variants,id'],
            'date'       => ['required','date_format:Y-m-d'],
            'start_local'=> ['required','date_format:H:i'], // "HH:MM" МСК
            'client_name'=> ['required','string','min:2','max:128'],
            'client_phone'=>['required','string','min:5','max:64'],
        ];
    }

    public function authorize(): bool { return true; }
}

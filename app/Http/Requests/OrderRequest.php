<?php

namespace App\Http\Requests;

use App\Models\Currency;
use App\Models\Customerpoints;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class OrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user) {
                        return $fail('No such user');
                    }
                }
            ],
            'amount' => [
                'required',
                'numeric',
                'regex:/^\d+\.\d{2}$/',
            ],
            'currency' => [
                'required',
                'size:3',
                function ($attribute, $value, $fail) {
                    $currency = Currency::where([
                        'currency_name' => $value
                    ])->first();
                    if (!$currency) {
                        return $fail('This Currency is currently not supported');
                    }
                }
            ],
            'points' => [
                'numeric',
                function ($attribute, $value, $fail) {
                    $userId = request()->input('user_id');
                    $available_points = User::find($userId)->points()->first();
                    if (!$available_points) {
                        return $fail('Currently you have no points. Please purchase something with us !');
                    }
                    if ($value > $available_points->points) {
                        return $fail('You only have ' . $available_points->points . ' points at the moment. Please shop with us to collect more points !');
                    }
                    //check expiry points
                    $expiryDate = Carbon::parse($available_points->points_expiry);
                    $now = Carbon::now();
                    if ($now->isAfter($expiryDate)) {
                        return $fail('Points have expired');
                    }
                }
            ]
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Please enter user_id',
            'amount.required' => 'The amount  is required!',
            'amount.regex' => "The amount format is invalid. Please enter two decimals places. Example: 7000.00 , 7000.50"
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => false
        ], 422));
    }
}

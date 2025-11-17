<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_method' => 'required|in:card,convenience_store',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $profile = optional($this->user())->profile;
            $postal  = trim((string) optional($profile)->postal_code);
            $addr    = trim((string) optional($profile)->address);

            if (!$profile || $postal === '' || $addr === '') {
                $validator->errors()->add('shipping', '配送先を登録してください');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'payment_method' => '支払い方法',
            'shipping'       => '配送先',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
        ];
    }
}

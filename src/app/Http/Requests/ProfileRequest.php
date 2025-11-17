<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'profile_image' => ['nullable', 'file', 'mimes:jpeg,png'],
            'name'      => ['required', 'string', 'max:20'],
            'postal_code'   => ['required', 'regex:/^\d{3}-\d{4}$/', 'size:8'],
            'address'       => ['required', 'string', 'max:255'],
            'building'      => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_image.mimes' => '拡張子は、.jpeg もしくは .png で登録してください',
            'name.required'   => 'ユーザー名を入力してください',
            'name.max'        => 'ユーザー名は、20文字以内で入力してください',
            'postal_code.required'=> '郵便番号を入力してください',
            'postal_code.regex'   => '郵便番号は「123-4567」の形式で入力してください',
            'postal_code.size'    => '郵便番号はハイフン含め8文字で入力してください',
            'address.required'    => '住所を入力してください',
        ];
    }
}

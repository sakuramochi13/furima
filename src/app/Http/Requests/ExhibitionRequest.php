<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'image'         => ['required', 'file', 'image', 'mimes:jpeg,png'],
            'category_ids'   => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'condition'     => ['required','in:excellent,very_good,good,poor'],
            'product_name'    => ['required','string','max:120'],
            'description'   => ['required', 'string', 'max:255'],
            'price'         => ['required', 'numeric', 'min:0'],
            'brand_name'    => ['nullable','string','max:255'],
        ];
    }

        public function attributes(): array
    {
        return [
            'image'          => '商品画像',
            'category_ids'   => '商品のカテゴリー',
            'condition'      => '商品の状態',
            'product_name'   => '商品名',
            'description'    => '商品説明',
            'price'          => '販売価格',
            'brand_id'       => 'ブランド名',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => ':attributeを登録してください',
            'image.image'    => ':attributeは画像ファイルを指定してください',
            'image.mimes'    => ':attributeはjpegまたはpngを指定してください',

            'category_ids.required' => ':attributeを選択してください',

            'condition.required' => ':attributeを選択してください',

            'name.required'        => ':attributeを入力してください',
            'description.required' => ':attributeを入力してください',
            'description.max'      => ':attributeは:max文字以内で入力してください',

            'price.required' => ':attributeを入力してください',
            'price.numeric'  => ':attributeは数値で入力してください',
            'price.min'      => ':attributeは:min以上で入力してください',
        ];
    }
}

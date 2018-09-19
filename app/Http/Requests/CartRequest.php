<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sku_id' => [
                'required',
                //'exists:product_skus,id',
                function($attribute, $value, $fail){
                    $sku = ProductSku::find($value);
                    if(!$sku){
                        return $fail('此商品不存在');
                    }
                    if(!$sku->product->on_sale){
                        return $fail('此商品未上架');
                    }
                    if($sku->amount  === 0 ){
                        return $fail('此商品已售完');
                    }
                    if($this->input('amount')>0&&$sku->stock < $this->input('amount')){
                        return $fail('库存不足');
                    }
                }
            ],

        'amount' =>'required|integer|min:1'
            //
        ];
    }

    public function messages()
    {

        return [
            'sku_id.required' => '请选择商品',
        ];

    }

    public function attributes()
    {
        return [
            'amount' => '商品数量'
        ];
    }

}

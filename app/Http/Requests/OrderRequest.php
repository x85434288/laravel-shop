<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
            'address_id'=>[
                'required',
                Rule::exists('user_addresses','id')->where('user_id', $this->user()->id),
            ],
            'items'=>'required|array',
            'items.*.sku_id' => [
                'required',
                function($attribute, $value, $fail){
                    if(!$sku = ProductSku::find($value)){
                        $fail('此商品不存在');
                        return;
                    }
                    if(!$sku->product->on_sale){
                        $fail('此商品未上架');
                        return;
                    }
                    if($sku->stock === 0 ){
                        $fail('此商品库存不足');
                        return;
                    }
                    preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);
                    $index = $m[1];
                    $amount = $this->input('items')[$index]['amount'];
                    if($amount > 0 && $sku->stock < $amount){
                        $fail('此商品库存不足');
                        return;
                    }

                }

            ],
            'items.*.amount'=> 'required|integer|min:1',
            //
        ];
    }

    public function attributes()
    {
        return [
            'items' => '商品'
        ];
    }
}

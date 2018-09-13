<?php

use Illuminate\Database\Seeder;

class UserAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        //数据工厂制造测试数据
        factory(App\Models\UserAddress::class, 20)->create();

    }
}

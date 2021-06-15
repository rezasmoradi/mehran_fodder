<?php

use App\Address;
use Illuminate\Database\Seeder;

class AddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Address::query()->count()) {
            Address::query()->truncate();
        }
        for ($i = 1; $i <= 10; $i++) {
            $this->createAddressForCustomer($i);
        }
        for ($i = 11; $i <= 13; $i++) {
            $this->createAddressForSeller($i);
        }
    }

    private function createAddressForCustomer($num)
    {
        Address::query()->create([
            'user_id' => $num,
            'province' => $num > 6 ? 'ایلام' : 'کرمانشاه',
            'city' => $num > 6 ? 'میشخاص' : 'روانسر',
            'street' => $num > 7 ? null : 'جام جم',
            'postal_code' => rand(1000000000, 9999999999)
        ]);
        $this->command->info('آدرس برای مشتری ایجاد شد.');
    }

    private function createAddressForSeller($num)
    {

        Address::query()->create([
            'user_id' => $num,
            'province' => 'ایلام',
            'city' => 'دهلران',
            'postal_code' => rand(1000000000, 9999999999)
        ]);
        $this->command->info('آدرس ها برای فروشنده ایجاد شد.');

    }
}

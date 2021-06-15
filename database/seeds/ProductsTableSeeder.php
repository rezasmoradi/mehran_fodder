<?php

use App\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Product::query()->count()) {
            Product::query()->truncate();
        }


        Product::query()->create([
            'name' => 'یونجه',
            'stock' => 1000000,
            'unit_price' => 90000,
            'discount' => 500,
            'packing_weight' => 30
        ]);

        Product::query()->create([
            'name' => 'یونجه',
            'stock' => 500000,
            'unit_price' => 200000,
            'packing_weight' => 100
        ]);

        Product::query()->create([
            'name' => 'ذرت',
            'stock' => 200000,
            'unit_price' => 150000,
            'packing_weight' => 50
        ]);

        Product::query()->create([
            'name' => 'ذرت',
            'stock' => 250000,
            'unit_price' => 300000,
            'packing_weight' => 100
        ]);

        $this->command->info('محصولات ذرت و یونجه ایجاد شد.');
    }
}

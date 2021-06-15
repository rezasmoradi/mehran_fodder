<?php

use App\Order;
use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Order::query()->count()) {
            Order::query()->truncate();
        }

        for ($i = 1; $i <= 10; $i++) {
            $this->createOrderForSale($i);
        }
        for ($i = 11; $i <= 13; $i++) {
            $this->createOrderForPurchase($i);
        }
    }

    private function createOrderForSale($num)
    {
        Order::query()->create([
            'user_id' => $num,
            'product_id' => $num < 5 ? 1 || 2 : 3 || 4,
            'employee_id' => $num < 5 ? 14 : 15,
            'total_amount' => 9000 / $num,
            'payable_amount' => 9000 / $num * 3000,
            'discount' => $num < 4 ? 1000 : 0,
            'pre_payment' => $num < 5 ? (9000 / $num * 2000) * 0.1 : (9000 / $num * 2000) * random_int(0.1, 1),
            'type' => Order::TYPE_SALE,
        ]);
    }

    private function createOrderForPurchase($num)
    {
        Order::query()->create([
            'user_id' => $num,
            'product_id' => $num === 11 ? 1 || 2 : 3,
            'employee_id' => 4,
            'total_amount' => 60000,
            'pre_payment' => 240000000,
            'type' => Order::TYPE_PURCHASE,
        ]);
        $this->command->info('سفارش خرید و فروش محصول ایجاد شد.');
    }
}

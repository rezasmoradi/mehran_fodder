<?php

use App\Payment;
use Illuminate\Database\Seeder;

class PaymentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Payment::query()->count()) {
            Payment::query()->truncate();
        }

        for ($i = 1; $i <= 13; $i++){
            $this->createPayment($i);
        }
    }

    private function createPayment($num)
    {
        Payment::query()->create([
            'order_id' => $num,
            'ref_id' => generate_payment_number(),
            'payed_amount' => $num < 5 ? (9000 / $num * 2000) * 0.1 : (9000 / $num * 2000) * random_int(0.1, 1),
            'method' => $num > 8 ? Payment::TYPE_PAYMENT_GATEWAY : Payment::TYPE_CARD_TO_CARD,
            'status' => $num >= 3 ? 1 : 0,
        ]);
    }
}

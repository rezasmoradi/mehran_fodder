<?php

use App\Transportation;
use Illuminate\Database\Seeder;

class TransportationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Transportation::query()->count()) {
            Transportation::query()->truncate();
        }

        for ($i = 1; $i <= 13; $i++){
            $this->createTransportationRecord($i);
        }

        $this->command->info('حمل و نقل ها ایجاد شد.');

    }

    private function createTransportationRecord($num)
    {
        Transportation::query()->create([
            'order_id' => $num,
            'license_plate' => random_int(10000, 99999),
            'vehicle_name' => $num > 8 ? 'تریلی' : 'کامیونت',
            'delivery_amount' => $num > 8 ? 12000 : 2500,
            'delivery_at' => now()->subDays($num + 4),
        ]);

    }
}

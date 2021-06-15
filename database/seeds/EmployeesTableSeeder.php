<?php

use App\Employee;
use Illuminate\Database\Seeder;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 14; $i <= 18; $i++) {
            $this->setEmployeesValues($i);
        }
    }

    private function setEmployeesValues($num)
    {
        Employee::query()->create([
            'user_id' => $num,
            'employee_code' => generate_employee_code(),
            'employed_at' => now()->subYears($num - 13),
        ]);
        $this->command->info('کد کارمندی و تاریخ استخدام برای کارمندان ایجاد شد.');
    }
}

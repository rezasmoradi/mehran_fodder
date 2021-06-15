<?php

use App\Employee;
use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::query()->count()) {
            User::query()->truncate();
            Employee::query()->truncate();
        }

        $this->createCustomerUser();
        $this->createSellerUser();

        $this->createOrderResponsibleEmployee();
        $this->createAccountantEmployee();
        $this->createWarehouseKeeperEmployee();
        $this->createAdminEmployee();
    }

    private function createCustomerUser()
    {
        factory(User::class, 10)->create([
            'type' => User::TYPE_CUSTOMER,
        ]);
        $this->command->info('مشتریان ایجاد شدند.');
    }

    private function createSellerUser()
    {
        factory(User::class, 3)->create([
            'type' => User::TYPE_SELLER,
        ]);
        $this->command->info('فروشندگان ایجاد شدند.');
    }

    private function createOrderResponsibleEmployee()
    {
        factory(User::class, 2)->create([
            'type' => User::TYPE_ORDER_RESPONSIBLE,
        ]);
        $this->command->info('مسئولین سفارش شرکت ایجاد شدند.');
    }

    private function createAccountantEmployee()
    {
        $accountant = User::query()->create([
            'type' => User::TYPE_ACCOUNTANT,
            'first_name' => 'حسابدار',
            'last_name' => 'شرکت',
            'username' => 'accountant',
            'password' => bcrypt(123456),
            'mobile' => '09184237421',
        ]);
        $accountant->save();
        $this->command->info('حسابدار شرکت ایجاد شد.');
    }

    private function createWarehouseKeeperEmployee()
    {
        User::query()->create([
            'type' => User::TYPE_WAREHOUSE_KEEPER,
            'first_name' => 'انباردار',
            'last_name' => 'شرکت',
            'username' => 'warehouse',
            'password' => bcrypt(123456),
            'mobile' => '09111111111',
        ]);
        $this->command->info('انباردار شرکت ایجاد شد.');
    }

    private function createAdminEmployee()
    {
        User::query()->create([
            'type' => User::TYPE_ADMIN,
            'first_name' => 'مدیر',
            'last_name' => 'اصلی',
            'username' => 'admin',
            'password' => bcrypt(123456),
            'mobile' => '09100000000',
        ]);
        $this->command->info('مدیر اصلی سایت ایجاد شد.');
    }
}

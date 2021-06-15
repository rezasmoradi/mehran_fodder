<?php

use App\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('ref_id', 100)->unique();
            $table->string('mobile', 11)->nullable();
            $table->unsignedDecimal('payed_amount', 15, 3);
            $table->enum('method', Payment::PAYMENT_TYPES)->default(Payment::TYPE_PAYMENT_GATEWAY);
            $table->tinyInteger('status')->default(0);
            $table->string('descriptions', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade')
                ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}

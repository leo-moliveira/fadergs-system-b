<?php

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
            $table->integer('client_id')->nullable(false);
            $table->integer('reservation_id')->nullable(false);
            $table->integer('room_number')->nullable(false);
            $table->string('description',500);
            $table->double('price')->nullable(false);
            $table->string('status',60)->nullable(false);
            $table->string('pay_code',60)->nullable(true);
            $table->timestamp('pay_date')->nullable(true);
            $table->timestamp('date_start')->nullable(false);
            $table->timestamp('date_end')->nullable(false);
            $table->timestamps();
        });
    }
//user_id, reservation_id, room_number, desc, price, , , , ,
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

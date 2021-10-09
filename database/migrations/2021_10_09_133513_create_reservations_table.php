<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('id')->unique()->nullable(false);
            $table->integer('client_id')->nullable(false);
            $table->integer('room_id')->nullable(false);
            $table->timestamp('date_start')->nullable(false);
            $table->timestamp('date_end')->nullable(false);
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->double('price')->nullable(false);
            $table->string('status',60)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations');
    }
}

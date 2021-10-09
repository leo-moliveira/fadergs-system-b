<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCleaningTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cleaning', function (Blueprint $table) {
            $table->increments('id')->unique()->nullable(false);
            $table->integer('rooms_id')->nullable(false);
            $table->integer('manager_id')->nullable(false);
            $table->integer('employee_id')->nullable(false);
            $table->timestamp('cleaning_date')->nullable();
            $table->string('status',60);
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
        Schema::dropIfExists('cleaning');
    }
}

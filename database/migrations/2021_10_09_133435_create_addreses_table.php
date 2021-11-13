<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddresesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addreses', function (Blueprint $table) {
            $table->increments('id')->unique()->nullable(false);
            $table->integer('user_id')->nullable();
            $table->string('address',200)->nullable(false);
            $table->integer('number')->nullable(false);
            $table->string('complement',100)->nullable(false);
            $table->string('city',100)->nullable(false);
            $table->string('state',100)->nullable(false);
            $table->string('country',100)->nullable(false);
            $table->integer('zip_code')->nullable(false);
            $table->boolean('active');
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
        Schema::dropIfExists('addreses');
    }
}

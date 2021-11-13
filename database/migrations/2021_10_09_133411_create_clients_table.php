<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->string('first_name',200);
            $table->string('last_name',200);
            $table->string('full_name',400);
            $table->string('email',200);
            $table->string('cpf',20)->nullable(false);
            $table->string('rg',20)->nullable(true);
            $table->string('gender',100);
            $table->boolean('status');
            $table->timestamp('last_reservation')->nullable(true);
            $table->timestamp('registration_date');
            $table->timestamps();
            $table->primary('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}

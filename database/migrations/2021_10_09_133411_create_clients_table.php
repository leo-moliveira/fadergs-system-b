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
            $table->string('email',200);
            $table->integer('cpf')->nullable(false);
            $table->integer('rg')->nullable(false);
            $table->string('gender',100);
            $table->boolean('status');
            $table->timestamp('last_reservation');
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

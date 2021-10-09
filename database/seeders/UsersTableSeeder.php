<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon as Carbon;

class UsersTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Add the master administrator, user id of 1
        $users = [
            [
                'user_name' => 'admin',
                'password' =>app('hash')->make('1234'),
                'status' => true,
                'created_at' => Carbon::now()
            ],
            [
                'user_name' => 'gerente',
                'password' =>app('hash')->make('1234'),
                'status' => true,
                'created_at' => Carbon::now()
            ],
            [
                'user_name' => 'camareira',
                'password' =>app('hash')->make('1234'),
                'status' => true,
                'created_at' => Carbon::now()
            ],
        ];

        DB::table('users')->insert($users);
    }

}

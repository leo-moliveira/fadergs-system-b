<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon as Carbon;

class EmployeesTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Add the master administrator, user id of 1
        $employees = [
            [
                'user_id' => 1,
                'first_name' => "Happy",
                'last_name' => "Wilburn",
                'status' => 1,
                'created_at' => Carbon::now()
            ],
            [
                'user_id' => 2,
                'first_name' => "Kenton",
                'last_name' => "Lucia",
                'status' => 1,
                'created_at' => Carbon::now()
            ],
            [
                'user_id' => 3,
                'first_name' => "Lorin",
                'last_name' => "Temperance",
                'status' => 1,
                'created_at' => Carbon::now()
            ],
        ];

        DB::table('employees')->insert($employees);
    }
}

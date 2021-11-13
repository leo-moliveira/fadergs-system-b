<?php

namespace Database\Seeders;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomTableSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            [
                'id' => 101,
                'status' => 0,
                'description' => "2 beds and 1 bathroom",
                'price' => '100.00',
                'created_at' => Carbon::now()
            ],
            [
                'id' => 102,
                'status' => 0,
                'description' => "1 double bad and 1 bathroom",
                'price' => '200.00',
                'created_at' => Carbon::now()
            ],
            [
                'id' => 103,
                'status' => 0,
                'description' => "1 double bad, 1 bad and 2 bathroom",
                'price' => '350.00',
                'created_at' => Carbon::now()
            ],

        ];
        DB::table('rooms')->insert($rooms);
    }
}

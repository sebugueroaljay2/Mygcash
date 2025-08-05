<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChargeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('charge_types')->insert([
            ['amount' => '1-250', 'charges' => 5],
            ['amount' => '251-500', 'charges' => 10],
            ['amount' => '501-750', 'charges' => 15],
            ['amount' => '751-1000', 'charges' => 20],
            ['amount' => '1001-1250', 'charges' => 25],
            ['amount' => '1251-1500', 'charges' => 30],
            ['amount' => '1501-1750', 'charges' => 35],
            ['amount' => '1751-2000', 'charges' => 40],
            ['amount' => '2001-2250', 'charges' => 45],
            ['amount' => '2251-2500', 'charges' => 50],
            ['amount' => '2501-2750', 'charges' => 55],
            ['amount' => '2751-3000', 'charges' => 60],
            ['amount' => '3001-3250', 'charges' => 65],
            ['amount' => '3251-3500', 'charges' => 70],
            ['amount' => '3501-3750', 'charges' => 75],
            ['amount' => '3751-4000', 'charges' => 80],
            ['amount' => '4001-4250', 'charges' => 85],
            ['amount' => '4251-4500', 'charges' => 90],
            ['amount' => '4501-4750', 'charges' => 95],
            ['amount' => '4751-5000', 'charges' => 100],
        ]);
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('transaction_types')->insert([
            [
                'name' => 'Cash In',
            ],
            [
                'name' => 'Cash Out',
            ],
          
        ]);
    }
}
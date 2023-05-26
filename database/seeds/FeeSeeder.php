<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Fee;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFees = [
            [
                'transaction_type' => 'transfer',
                'amount' => 1.00,
            ],
            [
                'transaction_type' => 'exchange',
                'amount' => 1.00,
            ],
            [
                'transaction_type' => 'deposit',
                'amount' => 0.50,
            ],
        ];

        foreach ($defaultFees as $defaultFee) {
            Fee::create($defaultFee);
        }
    }
}

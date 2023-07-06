<?php

namespace Database\Seeders;

use App\Models\Orderstatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'status_name' => 'Not processed'
            ],
            [
                'status_name' => 'Pending'
            ],
            [
                'status_name' => 'Completed'
            ]
        ];
        foreach ($statuses as $status) {
            Orderstatus::create($status);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class CurrencySeeder extends Seeder
{
    protected $currencyService;

    public function __construct(CurrencyService $cs)
    {
        $this->currencyService = $cs;
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Currency::factory()->count(10)->create();
        $response = $this->currencyService->getCurrencies();
        $data = json_decode($response);
        if ($data) {
            $rates = $data->rates;
            foreach ($rates as $key => $rate) {
                # code...
                Currency::create([
                    'currency_name' => $key,
                    'exchange_rate' => $rate
                ]);
            }
        }
    }
}

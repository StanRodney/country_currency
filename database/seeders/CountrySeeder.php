<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use Carbon\Carbon;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Nigeria',
                'capital' => 'Abuja',
                'region' => 'Africa',
                'population' => 206139589,
                'currency_code' => 'NGN',
                'exchange_rate' => 1.00,
                'estimated_gdp' => 477000000000,
                'flag_url' => 'https://flagcdn.com/w320/ng.png',
                'last_refreshed_at' => Carbon::now(),
            ],
            [
                'name' => 'United States',
                'capital' => 'Washington, D.C.',
                'region' => 'Americas',
                'population' => 331002651,
                'currency_code' => 'USD',
                'exchange_rate' => 1.00,
                'estimated_gdp' => 25000000000000,
                'flag_url' => 'https://flagcdn.com/w320/us.png',
                'last_refreshed_at' => Carbon::now(),
            ],
            [
                'name' => 'United Kingdom',
                'capital' => 'London',
                'region' => 'Europe',
                'population' => 68207116,
                'currency_code' => 'GBP',
                'exchange_rate' => 1.22,
                'estimated_gdp' => 3200000000000,
                'flag_url' => 'https://flagcdn.com/w320/gb.png',
                'last_refreshed_at' => Carbon::now(),
            ],
            [
                'name' => 'China',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'population' => 1439323776,
                'currency_code' => 'CNY',
                'exchange_rate' => 0.14,
                'estimated_gdp' => 17900000000000,
                'flag_url' => 'https://flagcdn.com/w320/cn.png',
                'last_refreshed_at' => Carbon::now(),
            ],
            [
                'name' => 'Japan',
                'capital' => 'Tokyo',
                'region' => 'Asia',
                'population' => 125836021,
                'currency_code' => 'JPY',
                'exchange_rate' => 0.0067,
                'estimated_gdp' => 4200000000000,
                'flag_url' => 'https://flagcdn.com/w320/jp.png',
                'last_refreshed_at' => Carbon::now(),
            ],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}

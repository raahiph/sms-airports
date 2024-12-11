<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $airports = [
            [
                'code' => 'MLE',
                'name' => 'Velana International Airport',
                'country' => 'Maldives',
            ],
            [
                'code' => 'GAN',
                'name' => 'Gan International Airport',
                'country' => 'Maldives',
            ],
            [
                'code' => 'KDM',
                'name' => 'Kaadedhdhoo Airport',
                'country' => 'Maldives',
            ],
        ];

        foreach ($airports as $airport) {
            // First check if airport exists
            if (!Airport::where('code', $airport['code'])->exists()) {
                Airport::create([
                    'code' => $airport['code'],
                    'name' => $airport['name'],
                    'country' => $airport['country'],
                ]);
            }
        }
    }
}
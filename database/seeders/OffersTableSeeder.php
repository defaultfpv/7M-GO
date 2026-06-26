<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;

class OffersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Offer::create([
            'type' => 'other',
            'title' => 'Другое',
            'description' => null,
            'price' => null,
        ]);
    }
}
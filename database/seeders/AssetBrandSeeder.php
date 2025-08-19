<?php

namespace Database\Seeders;

use App\Models\AssetBrand;
use App\Models\AssetModel;
use Illuminate\Database\Seeder;

class AssetBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Carrier' => [
                'description' => 'Leading manufacturer of air conditioning, heating, and refrigeration systems.',
                'models' => [
                    ['name' => 'Infinity 21', 'model_number' => '25VNA1', 'btu_rating' => 24000, 'efficiency_rating' => '21 SEER'],
                    ['name' => 'Infinity 20', 'model_number' => '25VNA0', 'btu_rating' => 36000, 'efficiency_rating' => '20 SEER'],
                    ['name' => 'Performance 17', 'model_number' => '25HCB7', 'btu_rating' => 48000, 'efficiency_rating' => '17 SEER'],
                    ['name' => 'Comfort 16', 'model_number' => '25HCB6', 'btu_rating' => 60000, 'efficiency_rating' => '16 SEER'],
                    ['name' => 'Comfort 14', 'model_number' => '25HCB4', 'btu_rating' => 24000, 'efficiency_rating' => '14 SEER'],
                ],
            ],
            'Trane' => [
                'description' => 'Trusted HVAC systems for residential and commercial applications.',
                'models' => [
                    ['name' => 'XV20i', 'model_number' => '4TTV0', 'btu_rating' => 24000, 'efficiency_rating' => '22 SEER'],
                    ['name' => 'XV18', 'model_number' => '4TTR8', 'btu_rating' => 36000, 'efficiency_rating' => '18 SEER'],
                    ['name' => 'XR16', 'model_number' => '4TTR6', 'btu_rating' => 48000, 'efficiency_rating' => '16 SEER'],
                    ['name' => 'XR14', 'model_number' => '4TTR4', 'btu_rating' => 60000, 'efficiency_rating' => '14 SEER'],
                    ['name' => 'XR13', 'model_number' => '4TTR3', 'btu_rating' => 24000, 'efficiency_rating' => '13 SEER'],
                ],
            ],
            'Lennox' => [
                'description' => 'Premium heating and cooling solutions with innovative technology.',
                'models' => [
                    ['name' => 'Signature Series', 'model_number' => 'XC25', 'btu_rating' => 60000, 'efficiency_rating' => '26 SEER'],
                    ['name' => 'Elite Series', 'model_number' => 'XC21', 'btu_rating' => 48000, 'efficiency_rating' => '21 SEER'],
                    ['name' => 'Merit Series', 'model_number' => 'XC16', 'btu_rating' => 36000, 'efficiency_rating' => '16 SEER'],
                    ['name' => 'Merit Series', 'model_number' => 'XC14', 'btu_rating' => 24000, 'efficiency_rating' => '14 SEER'],
                    ['name' => 'Merit Series', 'model_number' => 'XC13', 'btu_rating' => 30000, 'efficiency_rating' => '13 SEER'],
                ],
            ],
            'Rheem' => [
                'description' => 'Reliable and efficient heating and cooling equipment.',
                'models' => [
                    ['name' => 'Prestige Series', 'model_number' => 'RP20', 'btu_rating' => 48000, 'efficiency_rating' => '20 SEER'],
                    ['name' => 'Classic Plus', 'model_number' => 'RA17', 'btu_rating' => 36000, 'efficiency_rating' => '17 SEER'],
                    ['name' => 'Classic Series', 'model_number' => 'RA16', 'btu_rating' => 24000, 'efficiency_rating' => '16 SEER'],
                    ['name' => 'Classic Series', 'model_number' => 'RA14', 'btu_rating' => 60000, 'efficiency_rating' => '14 SEER'],
                    ['name' => 'Value Series', 'model_number' => 'RA13', 'btu_rating' => 30000, 'efficiency_rating' => '13 SEER'],
                ],
            ],
            'Goodman' => [
                'description' => 'Affordable and dependable HVAC solutions for every home.',
                'models' => [
                    ['name' => 'GSXC18', 'model_number' => 'GSXC180', 'btu_rating' => 36000, 'efficiency_rating' => '18 SEER'],
                    ['name' => 'GSX16', 'model_number' => 'GSX160', 'btu_rating' => 48000, 'efficiency_rating' => '16 SEER'],
                    ['name' => 'GSX14', 'model_number' => 'GSX140', 'btu_rating' => 24000, 'efficiency_rating' => '14 SEER'],
                    ['name' => 'GSX13', 'model_number' => 'GSX130', 'btu_rating' => 60000, 'efficiency_rating' => '13 SEER'],
                    ['name' => 'GMSS96', 'model_number' => 'GMSS960', 'btu_rating' => 80000, 'efficiency_rating' => '96% AFUE'],
                ],
            ],
            'American Standard' => [
                'description' => 'High-quality HVAC systems with proven reliability.',
                'models' => [
                    ['name' => 'Platinum 20', 'model_number' => '4A7A0', 'btu_rating' => 48000, 'efficiency_rating' => '20 SEER'],
                    ['name' => 'Platinum 18', 'model_number' => '4A7A8', 'btu_rating' => 36000, 'efficiency_rating' => '18 SEER'],
                    ['name' => 'Gold 17', 'model_number' => '4A6B7', 'btu_rating' => 24000, 'efficiency_rating' => '17 SEER'],
                    ['name' => 'Silver 16', 'model_number' => '4A6B6', 'btu_rating' => 60000, 'efficiency_rating' => '16 SEER'],
                    ['name' => 'Silver 14', 'model_number' => '4A6B4', 'btu_rating' => 30000, 'efficiency_rating' => '14 SEER'],
                ],
            ],
        ];

        foreach ($brands as $brandName => $brandData) {
            $brand = AssetBrand::create([
                'name' => $brandName,
                'description' => $brandData['description'],
                'is_active' => true,
            ]);

            foreach ($brandData['models'] as $modelData) {
                AssetModel::create([
                    'asset_brand_id' => $brand->id,
                    'name' => $modelData['name'],
                    'model_number' => $modelData['model_number'],
                    'btu_rating' => $modelData['btu_rating'],
                    'efficiency_rating' => $modelData['efficiency_rating'],
                    'is_active' => true,
                ]);
            }
        }
    }
}

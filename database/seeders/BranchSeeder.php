<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the branch seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'code' => 'BR-0001',
                'name' => 'Chennai',
                'location' => 'Chennai',
                'status' => 'active',
                'phone' => '+91 44 2345 6789',
                'email' => 'chennai@horticulturecrm.com',
                'notes' => 'Head branch for Tamil Nadu operations.',
            ],
            [
                'code' => 'BR-0002',
                'name' => 'Bangalore',
                'location' => 'Bangalore',
                'status' => 'active',
                'phone' => '+91 80 2345 6789',
                'email' => 'bangalore@horticulturecrm.com',
                'notes' => 'Branch office for Karnataka operations.',
            ],
            [
                'code' => 'BR-0003',
                'name' => 'Coimbatore',
                'location' => 'Coimbatore',
                'status' => 'active',
                'phone' => '+91 422 2345 678',
                'email' => 'coimbatore@horticulturecrm.com',
                'notes' => 'Branch office for Western Tamil Nadu operations.',
            ],
            [
                'code' => 'BR-0004',
                'name' => 'Hyderabad',
                'location' => 'Hyderabad',
                'status' => 'active',
                'phone' => '+91 40 2345 6789',
                'email' => 'hyderabad@horticulturecrm.com',
                'notes' => 'Branch office for Telangana and Andhra Pradesh operations.',
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::updateOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
        }
    }
}

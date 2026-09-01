<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'swift-erp'],
            [
                'name'     => 'Swift ERP Demo',
                'email'    => 'info@swift-erp.local',
                'currency' => 'IDR',
                'address'  => 'Jakarta, Indonesia',
            ]
        );

        // Hubungkan admin ke perusahaan
        $admin = User::where('email', 'admin@swift-erp.local')->first();
        if ($admin) {
            $company->users()->syncWithoutDetaching([$admin->id]);
        }
    }
}

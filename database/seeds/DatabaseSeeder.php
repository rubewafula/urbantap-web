<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//         $this->call(ServiceProvidersSeeder::class);
//         $this->call(BusinessSeeder::class);
//         $this->call(ProviderImagesSeeder::class);
//         $this->call(OperatingHoursSeeder::class);
//         $this->call(ServicesSeeder::class);
//         $this->call(ProviderServicesSeeder::class);
//         $this->call(ExpertsSeeder::class);
//         $this->call(ReviewsSeeder::class);
//         $this->call(PortfolioSeeder::class);
//         $this->call(ProviderCategorySeeder::class);

        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UsersTableSeeder::class);
       $this->call(CategorySeeder::class);


    }
}

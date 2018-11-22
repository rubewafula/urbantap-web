<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class ProviderCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=100;$i++){
            $faker = Faker::create('App/ProviderCategory');
            DB::table('provider_categories')->insert([
                'service_provider_id' => $faker->numberBetween(1,100),
                'category_id' => $faker->numberBetween(1,2),
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

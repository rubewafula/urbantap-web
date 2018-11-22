<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class ProviderImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=400;$i++){
            $faker = Faker::create('App/ServiceProviderImages');
            DB::table('service_provider_images')->insert([
                'service_provider_id' => $faker->numberBetween(1,100),
                'image' => $faker->imageUrl(),
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

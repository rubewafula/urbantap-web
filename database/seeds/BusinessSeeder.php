<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=50;$i++){
            $faker = Faker::create('App/Business');
            DB::table('businesses')->insert([
                'service_provider_id' => $faker->numberBetween(1,50),
                'business_name' => $faker->company,
                'description' => $faker->paragraph,
                'location' => $faker->address,
                'lat' => $faker->latitude,
                'lng' => $faker->longitude,
                'phone_no' => $faker->phoneNumber,
                'facebook' => $faker->url,
                'instagram' => $faker->url,
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

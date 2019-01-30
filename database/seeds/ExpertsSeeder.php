<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class ExpertsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=100;$i++){
            $faker = Faker::create('App/Expert');
            DB::table('experts')->insert([
                'service_provider_id' => $faker->numberBetween(51,100),
                'business_description' => $faker->paragraph,
                'id_number' => $faker->isbn10,
                'home_location' => $faker->address,
                'work_phone_no' => $faker->phoneNumber,
                'work_location' => $faker->address,
                'work_lat' => $faker->latitude,
                'work_lng' => $faker->longitude,
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

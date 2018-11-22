<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class ServiceProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=100;$i++){
            $faker = Faker::create('App/ServiceProvider');
            DB::table('service_providers')->insert([
                'type' => $faker->numberBetween(1,2),
                'service_provider_name' => $faker->company,
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

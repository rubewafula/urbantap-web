<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class ProviderServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=300;$i++){
            $faker = Faker::create('App/ProviderServices');
            DB::table('provider_services')->insert([
                'service_provider_id' => $faker->numberBetween(1,100),
                'service_id' => $faker->numberBetween(1,10),
                'description' => $faker->sentence,
                'cost' => 'Ksh. '.$faker->numberBetween(100,2000),
                'duration' => '30 Mins',
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

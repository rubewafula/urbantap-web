<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=500;$i++){
            $faker = Faker::create('App/Portfolio');
            DB::table('portfolios')->insert([
                'service_provider_id' => $faker->numberBetween(1,100),
                'image_link' => $faker->imageUrl(),
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

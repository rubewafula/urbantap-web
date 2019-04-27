<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ReviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=500;$i++){
            $faker = Faker::create('App/Review');
            DB::table('reviews')->insert([
                'reviewer_id' => $faker->numberBetween(1,100),
                'service_provider_id' => $faker->numberBetween(1,100),
                'stars' => $faker->numberBetween(1,5),
                'review' => $faker->sentence,
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class OperatingHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=400;$i++){
            $faker = Faker::create('App/OperatingHours');
            DB::table('operating_hours')->insert([
                'service_provider_id' => $faker->numberBetween(1,100),
                'day' => $faker->dayOfWeek,
                'hours' => $faker->time() .' - '.$faker->time(),
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=10;$i++){
            $faker = Faker::create('App/Services');
            DB::table('services')->insert([
                'category_id' => $faker->numberBetween(1,2),
                'service_name' => $faker->word,
                'updated_at' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

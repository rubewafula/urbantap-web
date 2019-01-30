<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('users')->insert([
                'name' => "Dennis Muoki",
                'email' => "muokid3@gmail.com",
                'password' => '$2y$10$5Tf07ADgOESV4to4wTrq1.RLqtDQKzJRVge20.JqzyDAQbcsF6.wa', // muokid3
                'user_group' => 1,
                'phone_no' => "+254713653112",
                'remember_token' => null,
            ]);
    }
}

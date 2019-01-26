<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('user_groups')->truncate();
        $json = File::get("database/data/default_role.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            \App\UserGroup::firstOrCreate(array(
                'id' => $obj->id,
                'name' => $obj->name,
                'description' => $obj->description
            ));
        }
    }
}

<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Delete data before start seeding
        DB::table('users')->delete();
        
        
        //Inserting a fake user
        DB::table('users')->insert([
           'name'=>"Fake User",
            'email'=>"fake@123.com",
            'password'=> bcrypt('123456'),                       
        ]);
    }
}

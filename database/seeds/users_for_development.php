<?php

use Illuminate\Database\Seeder;

class users_for_development extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
          'name' => 'John Doe',
          'email' => 'joaopjt@gmail.com',
          'pass' => 'teste123',
          'api_key' => 'abc123'
        ]);
    }
}

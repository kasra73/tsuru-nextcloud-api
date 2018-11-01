<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::table('users')->delete();
        $users = array(
            [
                'name' => 'Kasra Fakhari',
                'email' => 'kasraf1373@gmail.com',
                'password' => Hash::make('ws8uLJSQpKxBpfuQ')
            ],
        );
        // Loop through each user above and create the record for them in the database
        foreach ($users as $user) {
            User::create($user);
        }
        Model::reguard();
    }
}

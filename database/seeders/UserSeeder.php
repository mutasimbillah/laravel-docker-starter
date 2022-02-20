<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder {
    public function run() {
        Model::unguard();
        $users = array(
            'admin@user.com'    => array(
                'name' => 'Admin',
                'role' => UserType::ADMIN,
            ),
            'merchant@user.com' => array(
                'name' => 'Merchant',
                'role' => UserType::MERCHANT,
            ),
            'waiter@user.com'   => array(
                'name' => 'Waiter',
                'role' => UserType::WAITER,
            ),
            'customer@user.com' => array(
                'name' => 'Customer',
                'role' => UserType::CUSTOMER,
            ),
        );
        $i = 0;
        foreach ($users as $email => $user) {
            /** @var User $model */
            $model = User::query()->create(array(
                'name'              => $user['name'],
                'password'          => bcrypt('secret'),
                'email'             => $email,
                'email_verified_at' => now(),
                'phone'             => '01675339460' . ++$i,
                'phone_verified_at' => now(),
            ));
            /** @var Role $role */
            $role = Role::query()->create(array(
                'name'         => $user['role'],
                'display_name' => ucfirst($user['role']),
            ));
            $model->attachRole($role);
        }
        Model::reguard();
    }
}

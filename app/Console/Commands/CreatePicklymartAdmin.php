<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreatePicklymartAdmin extends Command
{
    protected $signature = 'picklymart:create-admin {--name=} {--email=}';

    protected $description = 'Create the initial Picklymart administrator using a hidden password prompt.';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Administrator name')));
        $email = mb_strtolower(trim((string) ($this->option('email') ?: $this->ask('Administrator email'))));

        $validator = Validator::make(
            compact('name', 'email'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = (string) $this->secret('Administrator password (at least 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if (mb_strlen($password) < 12 || ! hash_equals($password, $confirmation)) {
            $this->error('Use a matching password with at least 12 characters. No account was created.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill(['is_admin' => true])->save();

        $this->info("Administrator account created for {$email}.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Provisions an account from the CLI. This is how the single owner account is
 * created on a fresh self-hosted install (public registration is closed by
 * default — see config/features.php). Credentials may be passed as options for
 * scripted/headless provisioning, or entered interactively when omitted.
 */
class CreateUser extends Command
{
    protected $signature = 'app:create-user
                            {--name= : The user\'s name}
                            {--email= : The user\'s email address}
                            {--password= : The user\'s password (min 8 characters)}';

    protected $description = 'Create a user account (used to provision the owner on a fresh install)';

    public function handle(): int
    {
        // Guard the common single-user case: don't silently add a second account.
        if (User::exists() && ! $this->confirm('A user already exists. This app is single-user by default — create another account anyway?', false)) {
            $this->comment('No user created.');

            return self::SUCCESS;
        }

        $name = $this->option('name') ?? text('Name', required: true);
        $email = Str::lower($this->option('email') ?? text('Email', required: true));
        $password = $this->option('password') ?? password('Password', required: true);

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => ['required', Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = $password; // hashed via the model's 'hashed' cast
        // Verify immediately so the owner clears the `verified` middleware
        // without any mail server configured on a fresh install.
        $user->email_verified_at = now();
        $user->save();

        $this->info("User created: {$user->email}");

        return self::SUCCESS;
    }
}

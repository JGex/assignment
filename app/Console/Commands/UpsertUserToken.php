<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpsertUserToken extends Command
{
    protected $signature = 'user:token:upsert
                            {email : Email of the user to renew}';
    protected $description = 'Create a token for a specific user, if he has no token, creat it. If the user do not exist, it will be created. If the user exist and have token, they will be revoked.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::firstOrCreate(
            ['email' => $email]
        );
        $token = $user->createToken('Product', ['api:product']);

        $user->tokens()->where('id', '!=', $token->accessToken->id)->delete();

        $this->info(sprintf('token generated successfully : %s', $token->plainTextToken));

        return self::SUCCESS;
    }
}

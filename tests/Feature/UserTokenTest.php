<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Tests\TestCase;

class UserTokenTest extends TestCase
{
//    use RefreshDatabase;

    public function test_user_token_command_arguments(): void
    {
        $this->expectException(RuntimeException::class);

        $this->artisan('user:token:upsert')
            ->expectsOutput('Not enough arguments (missing: "email").')
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('user', 0);
        $this->assertDatabaseCount('personal_access_token', 0);
    }

    public function test_user_token_command_success(): void
    {
        $this->artisan('user:token:upsert test@gmail.com')
            ->expectsOutputToContain('token generated successfully');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}

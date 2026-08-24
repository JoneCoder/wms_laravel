<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_user_and_organization()
    {
        $response = $this->postJson('/api/auth/register', [
            'organization_name' => 'Acme Corp',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success', 'message', 'data' => ['user', 'access_token', 'token_type']
        ]);

        $this->assertDatabaseHas('organizations', ['name' => 'Acme Corp']);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'warehouse_operator']);
    }

    public function test_can_login_user()
    {
        $org = Organization::create(['name' => 'Acme Corp']);
        $user = User::factory()->create([
            'organization_id' => $org->id,
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 'message', 'data' => ['user', 'access_token', 'token_type']
        ]);
    }
}

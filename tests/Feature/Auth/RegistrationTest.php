<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {

        //Seeing the roles into the test database
        $this->seed(RolesTableSeeder::class);

        //creating the user and assigning the admin role
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_cannot_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(403);
    }
}

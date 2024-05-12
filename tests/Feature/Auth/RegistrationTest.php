<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAccount($role)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    public function test_registration_screen_can_be_rendered(): void
    {

        //Seeing the roles into the test database
        $this->seed(RolesTableSeeder::class);

        //creating the user and assigning the admin role
        $admin = $this->makeAccount('admin');
        $response = $this->actingAs($admin)
            ->get('/register');

        $response->assertStatus(200);
    }

    public function test_admin_user_can_create_users(): void
    {
        //Seeing the roles into the test database
        $this->seed(RolesTableSeeder::class);

        //creating the user and assigning the admin role
        $admin = $this->makeAccount('admin');

        $response = $this->actingAs($admin)->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'writer',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('admin.index'));
    }

    public function test_new_users_cannot_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);

        $response->assertStatus(403);
    }

    public function test_duplicate_emails_cannot_be_registered(): void
    {
        $this->seed(RolesTableSeeder::class);

        $admin = $this->makeAccount('admin');
        //Creating a user with specific credentials
        $user = User::factory()->create([
            'name' => 'Test User 1',
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
        $user->assignRole('writer');

        $response = $this->actingAs($admin)->post('/register', [
            'name' => 'Test User 2',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);
        
        //validating first user exists
        $this->assertDatabaseHas('users', [
            'name' => 'Test User 1',
            'email' => 'test@example.com',
        ]);

        //Validating the second user is not stored in the database.
        $this->assertDatabaseMissing('users', [
            'name' => 'Test User 2',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect();

    }
}

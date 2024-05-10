<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Blog;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    private function makeAccount($role)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function makeBlog($user_id, $is_published = false)
    {
        $blog = Blog::factory()->create([
            'user_id' => $user_id,
            'is_published' => $is_published,
        ]);
        return $blog;
    }

    public function test_create_blog_with_writer()
    {
        $this->seed(RolesTableSeeder::class);
        $writer = $this->makeAccount('writer');
        $this->actingAs($writer);
        $response = $this->post(route('blog.store'), [
            'blog_title' => fake()->word(),
            'blog_content' => fake()->sentence(),
            'user_id' => $writer->id,
        ]);

        $response->assertRedirect(route('blog.owned', $writer->id))->assertSessionHas('message', 'Blog was created Successfully');
    }


    public function test_create_blog_with_admin()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $this->actingAs($admin);
        $response = $this->post(route('blog.store'), [
            'blog_title' => fake()->word(),
            'blog_content' => fake()->sentence(),
            'user_id' => $admin->id,
        ]);

        $response->assertRedirect(route('blog.owned', $admin->id))->assertSessionHas('message', 'Blog was created Successfully');
    }

    public function test_manager_cannot_create_blog()
    {
        $this->seed(RolesTableSeeder::class);
        $manager = $this->makeAccount('manager');
        $this->actingAs($manager);
        $response = $this->post(route('blog.store'), [
            'blog_title' => fake()->word(),
            'blog_content' => fake()->sentence(),
            'user_id' => $manager->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_create_user()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $this->actingAs($admin);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role'=>'writer'
        ]);
        $response->assertRedirect(route('admin.index', absolute: false));
        $this->assertDatabaseHas('users',[
            'email'=>'test@example.com'
        ]);
    }
}

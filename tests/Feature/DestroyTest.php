<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    private function makeAccount($role)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function makeBlog($user_id, $is_published=false)
    {
        $blog = Blog::factory()->create([
            'user_id'=> $user_id,
            'is_published'=>$is_published,
        ]);
        return $blog;
    }

    public function test_managers_cannot_delete_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $manager = $this->makeAccount('manager');
        $user = $this->makeAccount('writer');
        $blog = $this->makeBlog($user);

        $this->actingAs($manager);

        $response = $this->delete(route('blog.destroy', $blog->id));

        $response->assertStatus(403);
    }

    public function test_deleting_users_with_published_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $user = $this->makeAccount('writer');
        $this->makeBlog($user, true);

        $this->actingAs($admin);

        $response = $this->delete(route('admin.destroy', $user->id));

        $response->assertRedirectToRoute('admin.index')->assertSessionHas('success', 'User was disabled Successfully!');

    }

    public function test_deleting_users_with_unpublished_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $user = $this->makeAccount('writer');
        $this->makeBlog($user);

        $this->actingAs($admin);

        $response = $this->delete(route('admin.destroy', $user->id));

        $response->assertRedirectToRoute('admin.index')->assertSessionHas('success', 'User was deleted Successfully!');

    }

    public function test_admins_can_delete_unpublished_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $blog = $this->makeBlog($admin);
        $this->actingAs($admin);

        $response = $this->delete(route('blog.destroy', $blog->id));

        $response->assertRedirectToRoute('blog.owned', $admin->id)->assertSessionHas('success', 'Blog Deleted Successfully');
    }
}
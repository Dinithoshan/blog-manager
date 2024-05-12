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


    /*
        Attempt to delete blogs as the role manager.
    */
    public function test_managers_cannot_delete_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        //Creation of manager account and writer to create a blog
        $manager = $this->makeAccount('manager');
        $user = $this->makeAccount('writer');
        $blog = $this->makeBlog($user);

        $this->actingAs($manager);

        //Attempt for managers to delete blogs 
        $response = $this->delete(route('blog.destroy', $blog->id));

        //Assert that the database still has the blog
        $this->assertDatabaseHas('blogs', [
            'blog_title' => $blog->blog_title,
            'blog_content' => $blog->blog_content,
        ]);

        //Assert the forbidden http status
        $response->assertStatus(403);
    }

    /*
        Attempts to delete a user which has already published blogs
    */
    public function test_cannot_delete_users_with_published_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $user = $this->makeAccount('writer');
        $this->makeBlog($user, true);

        $this->actingAs($admin);

        // Attempt to delete the user
        $response = $this->delete(route('admin.destroy', $user->id));

        // Assert that the user's account is disabled but the account is present
        $this->assertDatabaseHas('users', [
            'name' => $user->name,
            'email' => $user->email,
            'active' => false,
        ]);

        // Assert that the response redirects to the admin index route with a success message
        $response->assertRedirectToRoute('admin.index')->assertSessionHas('success', 'User was disabled Successfully!');
    }


    /*
        Attempt to delete a user which has unpublished blogs
    */
    public function test_deleting_users_with_unpublished_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $user = $this->makeAccount('writer');
        $this->makeBlog($user);

        $this->actingAs($admin);
        //Attempt to delete a user with unpublished blogs 
        $response = $this->delete(route('admin.destroy', $user->id));

        //Assert the reponse redirects to admin.index route witht a success message
        $response->assertRedirectToRoute('admin.index')->assertSessionHas('success', 'User was deleted Successfully!');

        //Assert that the user has been removed from the database.
        $this->assertDatabaseMissing('users', [
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /*
        Attempt to delete unpublished blogs as admin
    */
    public function test_admins_can_delete_unpublished_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $blog = $this->makeBlog($admin);
        $this->actingAs($admin);

        //Attempt to delete unpublished blog as an admin
        $response = $this->delete(route('blog.destroy', $blog->id));

        //assert a redirect with success message
        $response->assertRedirectToRoute('blog.owned', $admin->id)->assertSessionHas('success', 'Blog Deleted Successfully');
        //assert that the database doesnt contain the blog and has been deleted
        $this->assertDatabaseMissing('blogs', [
            'blog_title' => $blog->blog_title,
            'blog_content' => $blog->blog_content,
        ]);
    }


    /*
        Attempt to Delete published blogs
    */
    public function test_cannot_delete_published_blogs(): void
    {
        $this->seed(RolesTableSeeder::class);
        $user = $this->makeAccount('admin');
        //Creating a published blog
        $blog = $this->makeBlog($user, true);

        $this->actingAs($user);

        //Attempt to delete a published blog as an admin
        $response = $this->delete(route('blog.destroy', $blog->id));

        //Assert a HTTP status of 403
        $response->assertStatus(403);

        //Assert that the puublished blog still exists in the database
        $this->assertDatabaseHas('blogs', [
            'id' => $blog->id,
        ]);
    }

    /*
        Attempt to remove the last admin user in the database
    */
    public function test_cannot_remove_last_admin_user(): void
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');

        $this->actingAs($admin);

        //Attempt to delete the last admin user
        $response = $this->delete(route('admin.destroy', $admin->id));

        // Assert that the response redirects back with an error message
        $response->assertRedirect()->assertSessionHas('error', 'Cannot remove admin role from the last admin user.');

        // Assert that the last admin user still exists in the database
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);

    }

    /*
        Function that takes the role as an argument and creates a user with that role
    */
    private function makeAccount($role)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    /*
        Function that takes the user id as an argument and the optional argument of is_published which defaults to false and makes a blog
    */
    private function makeBlog($user_id, $is_published = false)
    {
        $blog = Blog::factory()->create([
            'user_id' => $user_id,
            'is_published' => $is_published,
        ]);
        return $blog;
    }
}
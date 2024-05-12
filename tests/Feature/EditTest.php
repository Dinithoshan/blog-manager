<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Testing\Fakes\Fake;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase,WithFaker;

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
        Function that takes the option argument of is_published and makes a blog
    */
    private function makeBlog($is_published = false)
    {
        $blog = Blog::factory()->create(['is_published' => $is_published]);
        return $blog;
    }



    /*
        Attempt to edit user details from the role admin
    */
    public function test_admin_can_edit_user_details(): void 
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $user = $this->makeAccount('manager');

        $this->actingAs($admin);

        //attempt admin to edit user details
        $response = $this->put(route('admin.update', $user->id), [
            'name' => 'Updated User',
            'email' => 'updatedemail@blog.com',
            'role' => 'writer',
        ]);

        //Assert Redirect with success message
        $response->assertRedirect(route('admin.index'))->assertSessionHas('success', 'User Updated Successfully!');
        //validate that the user has been updated on the database
        $this->assertEquals('writer', $user->fresh()->roles()->first()->name);
        $this->assertDatabaseHas('users', [
            'name' => 'Updated User',
            'email' => 'updatedemail@blog.com',
        ]);

    }


    /*
        Attempt to Edit a unpublished blog with the role admin
    */
    public function test_admin_can_edit_blog(): void
    {
        //Creating an admin user an making an unpublished blog
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $blog = $this->makeBlog();
        $this->actingAs($admin);

        //Attempt to edit blog as admin
        $response = $this->put(route('blog.update', $blog->id), [
            'blog_title' => 'updated blog title',
            'blog_content' => 'updated blog content',
            'user_id' => $admin->id,
        ]);

        $response->assertRedirect(route('blog.owned', $admin->id))->assertSessionHas('success', 'Blog updated successfully');
        $this->assertDatabaseHas('blogs', [
            'blog_title' => 'updated blog title',
            'blog_content' => 'updated blog content',
            'user_id' => $admin->id,
        ]);

    }


    /*
        Attempt to edit a published blog with the role admin
    */
    public function test_published_blogs_cannot_be_edited(): void 
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $blog = $this->makeBlog(true);
        $this->actingAs($admin);

        $updatedBlog = [
            'blog_title' => 'updated blog title',
            'blog_content' => 'updated blog content',
            'user_id' => $admin->id,
        ];

        //Attempt to edit published blog
        $response = $this->put(route('blog.update', $blog->id), $updatedBlog);

        //ensuring a 403 and updated blog not stored
        $response->assertStatus(403);
        //Ensure Database doesnt contain attempted update
        $this->assertDatabaseMissing('blogs', $updatedBlog);

        //validating that the blog remains published
        $this->assertEquals(true, $blog->is_published);
    }


    /*
        Attempt to edit unpublished blog as the role manager
    */
    public function test_manager_cannot_edit_blog(): void 
    {
        $this->seed(RolesTableSeeder::class);
        $manager = $this->makeAccount('manager');
        $blog = $this->makeBlog();

        $this->actingAs($manager);


        $updatedBlog = [
            'blog_title' => 'updated blog title',
            'blog_content' => 'updated blog content',
            'user_id' => $manager->id,
        ];

        //Attempt to edit blog as a manager
        $response = $this->put(route('blog.update', $blog->id),$updatedBlog);

        //Assert the http status of the request to be forbidden
        $response->assertStatus(403);
        //Assert the database to check for the updated blog
        $this->assertDatabaseMissing('blogs', $updatedBlog);
    }


    /*
        Attempt to edit unpublished blog as the role writer
    */
    public function test_writers_can_edit_unpublished_blogs(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer = $this->makeAccount('writer');

        // Create a blog directly
        $blog = Blog::factory()->create([
            'user_id' => $writer->id,
            'is_published' => false // Ensure the blog is unpublished
        ]);

        $this->actingAs($writer);
        $updatedBlog=[
            'blog_title' => 'updated test title',
            'blog_content' => 'updated test content updated test content',
        ];

        //Attempt to eedit unpublished blogs
        $response = $this->put(route('blog.update', $blog->id),$updatedBlog);
        //Looking foer redirect to owned blog pages
        $response->assertStatus(302)->assertSessionDoesntHaveErrors();

        //vaidating Database has been updated
        $this->assertDatabaseHas('blogs', $updatedBlog);
    }

    /*
        Attempt to edit the blog and clear the blog content
    */
    public function test_cannot_edit_blogs_to_clear_content(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer=$this->makeAccount('writer');
        $blog=$this->makeBlog();

        $this->actingAs($writer);
        $updatedBlog=[
            'blog_title' => 'updated test title',
            'blog_content' => null,
        ];

        //Sending a blog to the update without the blog content
        $response=$this->put(route('blog.update', $blog->id), $updatedBlog);

        //redirect with session errors(validation)
        $response->assertStatus(302)->assertSessionHasErrors();

        //Database doesnt contain updated title
        $this->assertDatabaseMissing('blogs', $updatedBlog);

        //Database Contains original blog
        $this->assertDatabaseHas('blogs',[
            'id'=>$blog->id,
            'blog_title'=>$blog->blog_title,
            'blog_content'=>$blog->blog_content,
        ]);

    }


    /*
        Attempt to edit the blog and clear the blog title
    */
    public function test_cannot_edit_blogs_to_clear_title(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer=$this->makeAccount('writer');
        $blog=$this->makeBlog();

        $this->actingAs($writer);
        $updatedBlog=[
            'blog_content' => 'updated test content',
        ];

        //Sending a blog to the update without the blog content
        $response=$this->put(route('blog.update', $blog->id), $updatedBlog);

        //redirect with session errors(validation)
        $response->assertStatus(302)->assertSessionHasErrors();

        //Database doesnt contain updated title
        $this->assertDatabaseMissing('blogs', $updatedBlog);

        //Database Contains original blog
        $this->assertDatabaseHas('blogs',[
            'id'=>$blog->id,
            'blog_title'=>$blog->blog_title,
            'blog_content'=>$blog->blog_content,
        ]);
    }

    /*
        Attempt to edit the user details as the role writer
    */
    public function test_writers_cannot_edit_user_details(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer=$this->makeAccount('writer');
        $manager=$this->makeAccount('manager');

        $response=$this->actingAs($writer)->put(route('admin.update', $manager->id),[
            'name'=>'updated manager name',
            'email'=>'updatedmanager@blog.com',
            'role'=>'writer',
        ]);

        //validate the Forbidden Response
        $response->assertStatus(403);

        //validate Database users table is not updated
        $this->assertDatabaseMissing('users',[
            'name'=>'updated manager name',
            'email'=>'updatedmanager@blog.com',
        ]);

        //validate Database Roles table is not updated
        $this->assertFalse($manager->hasRole('writer'));
    }

    /*
        Attempt to edit the user details as the role manager
    */
    public function test_managers_cannot_edit_user_details(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer=$this->makeAccount('writer');
        $manager=$this->makeAccount('manager');

        //Attempt to chaneg the user details
        $response=$this->actingAs($manager)->put(route('admin.update', $writer->id),[
            'name'=>'updated writer name',
            'email'=>'updatedwriter@blog.com',
            'role'=>'manager',
        ]);

        //validate the Forbidden Response
        $response->assertStatus(403);

        //validate Database users table is not updated
        $this->assertDatabaseMissing('users',[
            'name'=>'updated writer name',
            'email'=>'updatedwriter@blog.com',
        ]);

        //validate Database Roles table is not updated
        $this->assertFalse($writer->hasRole('manager'));
    }

    /*
        Attempt to publish blogs as the role writer
    */
    public function test_writers_cannot_publish_blogs(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer=$this->makeAccount('writer');
        $this->actingAs($writer);
        $blog=$this->makeBlog();

        //Attempt to change the is_published state
        $response = $this->actingAs($writer)->put(route('blog.togglePublish', $blog->id));

        //Ensuring a 403
        $response->assertStatus(403);
        //validating that the blog is still in unpublished state
        $this->assertDatabaseHas('blogs',[
            'id'=>$blog->id,
            'is_published'=>false,
        ]);
    }

    /*
        Attempt to unpublish blogs as the role writer
    */
    public function test_writers_cannot_unpublish_blogs(): void
    {
        $this->seed(RolesTableSeeder::class);

        //Using an Admin account to create and publish a blog
        $admin=$this->makeAccount('admin');
        $this->actingAs($admin);
        $blog=$this->makeBlog(true);

        //Making a writer account
        $writer=$this->makeAccount('writer');

        //Attempt to change the is_published state
        $response = $this->actingAs($writer)->put(route('blog.toggleUnpublish', $blog->id));
        
        //Ensuring a 403
        $response->assertStatus(403);
        //validating that the blog is still in published state
        $this->assertDatabaseHas('blogs',[
            'id'=>$blog->id,
            'is_published'=>true,
        ]);
    }


    /*
        Attempt to change the admin role of the last admin user
    */
    public function test_last_admin_users_role_cannot_be_changed(): void
    {
        // Seed the database with a single admin user
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
    
        // Attempt to change the role of the last admin user
        $response = $this->actingAs($admin)->put(route('admin.update', $admin->id), [
            'name' => 'updated admin name',
            'email' => 'updatedadmin@example.com',
            'role' => 'manager', // Attempt to change role to manager
        ]);
    
        // Assert that the response redirects back with an error message
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot remove admin role from the last admin user.');
    
        // Assert that the admin user's role remains unchanged
        $this->assertTrue($admin->hasRole('admin'));
    }
}

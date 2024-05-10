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
    use RefreshDatabase;
    use WithFaker;
    //Private function to make seed and make accouunt with a role
    private function makeAccount($role)
    { 
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }


    //local function has optional argument of is_published that defaults to false unless specified
    private function makeBlog($is_published=false){
        $blog = Blog::factory()->create(['is_published'=>$is_published]);
        return $blog;
    }



    public function test_admin_can_edit_user_details ()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $user = $this->makeAccount('manager');

        $this->actingAs($admin);

        $response = $this->put(route('admin.update', $user->id),[
            'name'=>Fake()->name(),
            'email'=>Fake()->unique()->email(),
            'role'=>'writer',
        ]);

        $response->assertRedirect(route('admin.index'))->assertSessionHas('success', 'User Updated Successfully!');

        $this->assertEquals('writer', $user->fresh()->roles()->first()->name);
        
    }


    public function test_admin_can_edit_blog()
    {
        //Creating an admin user an making an unpublished blog
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $blog = $this->makeBlog();
        $this->actingAs($admin);

        $response = $this->put(route('blog.update', $blog->id),[
            'blog_title'=>Fake()->word(),
            'blog_content'=>Fake()->sentence(),
            'user_id'=>$admin->id,
        ]);

        $response->assertRedirect(route('blog.owned', $admin->id))->assertSessionHas('success', 'Blog updated successfully');

    }

    public function test_published_blogs_cannot_be_edited()
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $blog = $this->makeBlog(true);
        $this->actingAs($admin);

        $response = $this->put(route('blog.update', $blog->id),[
            'blog_title'=>Fake()->word(),
            'blog_content'=>Fake()->sentence(),
            'user_id'=>$admin->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_cannot_edit_blog()
    {
        $this->seed(RolesTableSeeder::class);
        $manager = $this->makeAccount('manager');
        $blog = $this->makeBlog();

        $this->actingAs($manager);

        $response = $this->put(route('blog.update', $blog->id),[
            'blog_title'=>Fake()->word(),
            'blog_content'=>Fake()->sentence(),
            'user_id'=>$manager->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_writers_can_edit_unpublished_blogs()
    {
        $this->seed(RolesTableSeeder::class);
        $writer = $this->makeAccount('writer');
    
        // Create a blog directly
        $blog = Blog::factory()->create([
            'user_id' => $writer->id,
            'is_published' => false // Ensure the blog is unpublished
        ]);
    
        $this->actingAs($writer);
    
        $response = $this->put(route('blog.update', $blog->id), [
            'blog_title' => 'test title',
            'blog_content' => 'test content test content',
        ]);
    
        $response->assertStatus(200);
    }
}

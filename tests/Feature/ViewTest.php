<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Database\Seeders\BlogTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ViewTest extends TestCase
{

    use RefreshDatabase;

    /*
        Private function to make an account taking the role as an argument
    */
    private function makeAccount($role){
        //Necessary for multiple use of the private method
        if (!Role::count()) {
            $this->seed(RolesTableSeeder::class);
        }
        //user creation
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }
    

    /*
        Private function to make blog which takes an optional argument of user ID which defaults to null
    */
    private function makeBlog($userId = null){
        //Takes an optional argument of userId to create blogs from a specific user
        $blog = Blog::factory()->create([
            'user_id' => $userId ?? User::factory()->create()->id,
        ]);
        return $blog;
    }
    


    /*
        Attempt to view all the contents of published blogs as role admin
    */
    public function test_admin_can_view_all_contents_of_published_blogs() :void
    {
        //making admin user
        $user=$this->makeAccount('admin');
        $blog = $this->makeBlog();
        $blog->update(['is_published'=>true]);
        //check http response for visiting the blog.index route as administrator 
        $response = $this->actingAs($user)
            ->get(route('blog.index'));

        //Ensuring Status code of view and view contains the content of the published blog
        $response->assertStatus(200);
        $response->assertSee($blog->blog_title);
        $response->assertSee($blog->blog_content);
        $response->assertSee($user->name);
    }


    /*
        Attempt to view all unpublished blogs as role admin 
    */
    public function test_admin_can_view_unpublished_blogs()  : void
    {
        //making admin user
        $user=$this->makeAccount('admin');
        $blog = $this->makeBlog();
        //check http response for visiting the unpublished blogs as administrator 
        $response = $this->actingAs($user)
            ->get(route('blog.unpublished'));

        //Ensuring Status code of view and view contains the content of the unpublished blog
        $response->assertStatus(200);
        $response->assertSee($blog->blog_title);
        $response->assertSee($blog->blog_content);
        $response->assertSee($user->name);
    }

    /*
        Attempt to view users page as role admin
    */
    public function test_admin_can_view_users() :void
    {
        $user=$this->makeAccount('admin');
        //check http response for visiting the unpublished blogs as admin
        $response = $this->actingAs($user)
            ->get(route('admin.index'));

        $response->assertStatus(200);
    }

    /*
        Attempt to view edit blogs page as role admin
    */
    public function test_admin_can_view_edit_blogs_page(): void
    {
        $admin=$this->makeAccount('admin');
        $blog=$this->makeBlog();
        $response = $this->actingAs($admin)
        ->get(route('blog.edit', $blog->id));
        //page doesnt render writer of the blog
        $response->assertStatus(200);
        $response->assertSee($blog->blog_title);
        $response->assertSee($blog->blog_content);
    }

    /*
        Attempt to view unpublished blogs page as role manager
    */
    public function test_manager_can_view_unpublished_blogs() :void
    {
        $manager=$this->makeAccount('manager');
        $writer=$this->makeAccount('writer');
        $this->actingAs($writer);
        $blog = $this->makeBlog($writer->id);
        $response = $this->actingAs($manager)->get(route('blog.unpublished'));
        $response->assertStatus(200);
        $response->assertSee($blog->blog_title);
        $response->assertSee($blog->blog_content);
        $response->assertSee($writer->name);
    }

    /*
        Attempt to view create blogs page as role writer
    */
    public function test_writer_can_view_create_page() :void
    {
        $user=$this->makeAccount('writer');
        $response = $this->actingAs($user)->get(route('blog.create'));
        $response->assertStatus(200);
    }

    /*
        Attempt to view owned blogs page as role writer
    */
    public function test_writer_can_view_owned_blogs () :void
    {
        $writer=$this->makeAccount('writer');
        $blog= $this->makeBlog($writer->id);
        $response = $this->actingAs($writer)->get(route('blog.owned', $writer->id));
        $response->assertStatus(200);
        $response->assertSee($blog->blog_title);
        $response->assertSee($blog->blog_content);
        $response->assertSee($writer->name);
    }

    /*
        Attempt to view unpublished blogs page as role writer
    */
    public function test_writer_cannot_view_unpublished_page():void
    {
        $writer = $this->makeAccount('writer');
        $response = $this->actingAs($writer)->get(route('blog.unpublished'));

        $response->assertStatus(403)->assertSee('Unauthorized');
    }

    /*
        Attempt to view users page as role writer
    */
    public function test_writer_cannot_view_users_page():void
    {
        $writer=$this->makeAccount('writer');
        $response = $this->actingAs($writer)->get(route('admin.index'));

        $response->assertStatus(403)->assertSee('Unauthorized');
    }

    /*
        Attempt to view users page as role manager
    */
    public function test_manager_cannot_view_users_page(): void
    {
        $manager= $this->makeAccount('manager');
        $response = $this->actingAs($manager)->get(route('admin.index'));

        $response->assertStatus(403)->assertSee('Unauthorized');
    }
}

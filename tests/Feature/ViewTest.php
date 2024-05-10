<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Database\Seeders\BlogTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewTest extends TestCase
{

    use RefreshDatabase;

    //repeated code to seed to database and make an account.
    private function makeAccount($role){
        $this->seed(RolesTableSeeder::class);
        //Create the user
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function makeBlog(){
        $blog = Blog::factory()->create();
        return $blog;
    }




    public function test_admin_can_view_published_blogs()
    {
        //making admin user
        $user=$this->makeAccount('admin');
        //check http response for visiting the blog.index route as administrator 
        $reponse = $this->actingAs($user)
            ->get(route('blog.index'));

        $reponse->assertStatus(200);

    }


    public function test_admin_can_view_unpublished_blogs()
    {
        //making admin user
        $user=$this->makeAccount('admin');
        //check http response for visiting the unpublished blogs as administrator 
        $response = $this->actingAs($user)
            ->get(route('blog.unpublished'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_users()
    {
        $user=$this->makeAccount('admin');
        //check http response for visiting the unpublished blogs as administrator 
        $response = $this->actingAs($user)
            ->get(route('admin.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_edit_blogs_page()
    {
        $user=$this->makeAccount('admin');
        $blog=$this->makeBlog();
        $response = $this->actingAs($user)
        ->get(route('blog.edit', $blog->id));

        $response->assertStatus(200);
    }

    public function test_manager_can_view_unpublished_blogs()
    {
        $user=$this->makeAccount('manager');
        $response = $this->actingAs($user)->get(route('blog.unpublished'));

        $response->assertStatus(200);
    }

    public function test_writer_can_view_create_page()
    {
        $user=$this->makeAccount('writer');
        $response = $this->actingAs($user)->get(route('blog.create'));


        $response->assertStatus(200);
    }

    public function test_writer_can_view_owned_blogs ()
    {
        $user=$this->makeAccount('writer');
        $response = $this->actingAs($user)->get(route('blog.owned', $user->id));

        $response->assertStatus(200);
    }
}

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



    /*
        Attempt to Create a blog with the role writer
    */
    public function test_create_blog_with_writer(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer = $this->makeAccount('writer');
        $this->actingAs($writer);


        $blog = [
            'blog_title' => 'custom test blog',
            'blog_content' => 'custom test blog content',
            'user_id' => $writer->id,
        ];
        $response = $this->post(route('blog.store', $blog));


        //Validate that the new blog has been created in the database.
        $this->assertDatabaseHas('blogs', $blog);
        $response->assertRedirect(route('blog.owned', $writer->id))->assertSessionHas('message', 'Blog was created Successfully');
    }



    /*
        Attempt to create a blog with the role admin
    */
    public function test_create_blog_with_admin(): void
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $blog = [
            'blog_title' => 'custom test blog',
            'blog_content' => 'custom test blog content',
            'user_id' => $admin->id,
        ];

        //attempt to create blog
        $this->actingAs($admin);
        $response = $this->post(route('blog.store'), $blog);

        //validate that the blog has been created
        $this->assertDatabaseHas('blogs', $blog);
        $response->assertRedirect(route('blog.owned', $admin->id))->assertSessionHas('message', 'Blog was created Successfully');
    }



    /*
        Attempt to Create blog as the role manager.
    */
    public function test_manager_cannot_create_blog(): void
    {
        $this->seed(RolesTableSeeder::class);
        $manager = $this->makeAccount('manager');
        $blog = [
            'blog_title' => 'custom test blog',
            'blog_content' => 'custom test blog content',
            'user_id' => $manager->id,
        ];

        $this->actingAs($manager);
        $response = $this->post(route('blog.store'), $blog);

        //Validate that the blog does not exist in thee database
        $this->assertDatabaseMissing('blogs', $blog);
        $response->assertStatus(403)->assertSee('Unauthorized');
    }

    /*
        Attempt too create a new user as role admin    
    */
    public function test_admin_create_user(): void
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');


        $this->actingAs($admin);
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'writer'
        ]);

        //Make sure that the new user is created in the database
        $response->assertRedirect(route('admin.index', absolute: false));
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }



    /*
        Attempt to create an user with the role writer        
    */
    public function test_writer_cannot_make_user(): void
    {
        $this->seed(RolesTableSeeder::class);
        $writer = $this->makeAccount('writer');


        $this->actingAs($writer);
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'writer'
        ]);

        //Make sure that only the writer user is in the database
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $response->assertStatus(403)->assertSee('Unauthorized');
    }



    /*
        Attempt to create a user with the role manager    
    */
    public function test_manager_cannot_create_user(): void
    {
        $this->seed(RolesTableSeeder::class);
        $manager = $this->makeAccount('manager');


        $this->actingAs($manager);
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'writer'
        ]);

        //Make sure that only the manager user is in the database
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $response->assertStatus(403)->assertSee('Unauthorized');
    }


    /*
        Attempt to create a new blog with an empty title
    */
    public function test_cannot_create_blog_with_empty_title(): void
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');


        $this->actingAs($admin);
        $blog = [
            'blog_title' => null,
            'blog_content' => 'custom test blog content',
            'user_id' => $admin->id,
        ];

        $response = $this->post(route('blog.store'), $blog);

        //Validation should fail and it should be redirected
        $response->assertStatus(302);
        //Assert that the session has errors
        $response->assertSessionHasErrors('blog_title');
        //Validate that the blog does not exist in the database
        $this->assertDatabaseMissing('blogs', $blog);
    }

    /*
        Attempt to create a blog with empty blog content    
    */
    public function test_cannot_create_blog_with_empty_content(): void
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');


        $this->actingAs($admin);
        $blog = [
            'blog_title' => 'blog test',
            'blog_content' => null,
            'user_id' => $admin->id,
        ];

        $response = $this->post(route('blog.store'), $blog);


        //validation should Fail and it should be redirected
        $response->assertStatus(302);
        //Assert that the session has errors
        $response->assertSessionHasErrors('blog_content');
        //Validate that the blog does not exist in thee database
        $this->assertDatabaseMissing('blogs', $blog);

    }

    /*
        Attempt to create a user with an email that already exists in the users table  
    */
    public function test_cannot_create_user_with_duplicate_email(): void
    {
        $this->seed(RolesTableSeeder::class);
        $admin = $this->makeAccount('admin');
        $this->actingAs($admin);
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'writer'
        ]);

        //Attempt to create user with a duplicate email address
        $response = $this->post('/register', [
            'name' => 'Test User 2',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'writer'
        ]);

        //validate that the new user is not stored in the database.
        $this->assertDatabaseMissing('users', [
            'name' => 'Test User 2',
        ])->assertDatabaseCount('users', 2);

        //validate there is a redirect with a session error
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }


    /*
        private function that takes the role as an argument and creates a user with that role
    */
    private function makeAccount($role)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    /*
        Private function that takes the user id and an optional argument of is_published which defaults to false and creates a blog
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

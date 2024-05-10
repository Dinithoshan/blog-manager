<?php

namespace Database\Seeders;

use App\Models\Blog;
use Database\Factories\BlogTableFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class BlogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // BlogTableFactory::factory(50)->create();
        Blog::factory(50)->create();
    }
}

@include('layouts.navi')
<div class="container mx-auto">
    <div class="max-w-md mx-auto mt-8">
        <h2 class="text-2xl font-bold mb-4">Create Blog</h2>
        <form action="{{ route('blog.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            <div class="mb-4">
                <label for="blog_title" class="block text-gray-700 font-bold mb-2">Blog Title</label>
                <input type="text" id="blog_title" name="blog_title" class="form-input w-full" placeholder="Enter blog title" required>
            </div>
            <div class="mb-6">
                <label for="blog_content" class="block text-gray-700 font-bold mb-2">Blog Content</label>
                <textarea id="blog_content" name="blog_content" class="form-textarea w-full" rows="6" placeholder="Enter blog content" required></textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
            </div>
        </form>
    </div>
</div>

@include('layouts.navi')
<h1 class="text-3xl font-bold mb-4">Edit Blog</h1>
<hr>

<form method="POST" action="{{ route('blog.update', $blog->id) }}">
    @csrf
    @method('PUT')
    <label for="blog_title" class="block font-bold mb-2">Blog Title</label>
    <input id="blog_title" type="text" class="form-input w-full" name="blog_title" value="{{ $blog->blog_title }}" required>

    <label for="blog_content" class="block font-bold mb-2 mt-4">Blog Content</label>
    <textarea id="blog_content" class="form-textarea w-full" name="blog_content" rows="6" required>{{ $blog->blog_content }}</textarea>

    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded mt-4">Update Blog</button>
</form>

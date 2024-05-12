@include('layouts.navi')
<h1 class="text-3xl font-bold mb-4">Update Blog</h1>
<hr>

@if ($errors->has('email'))
<div class="alert alert-danger">
    {{ $errors->first('blog_title') }}
</div>
@endif

<form method="POST" action="{{ route('blog.update', $blog->id) }}">
    @csrf
    @method('PUT')
    <label for="blog_title" class="block font-bold mb-2">Blog Title</label>
    <input id="blog_title" type="text" class="form-input w-full" name="blog_title" value="{{ $blog->blog_title }}" required>

    <label for="blog_content" class="block font-bold mb-2 mt-4">Blog Content</label>
    <textarea id="blog_content" class="form-textarea w-full" name="blog_content" rows="6" required>{{ $blog->blog_content }}</textarea>

    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded mt-4">Update Blog</button>
</form>
@can('delete-unpublised-article')
<form method="POST" action="{{ route('blog.destroy', $blog->id) }}" onsubmit="return confirm('Are you sure you want to delete this blog?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn bg-red-500 hover:bg-red-600  text-white font-bold py-2 px-4 mt-4 rounded">Delete</button>
</form>
@endcan

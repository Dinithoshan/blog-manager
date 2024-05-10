@include('layouts.navi')
<br>
<h1 class="text-3xl font-bold mb-4">My Blogs</h1>
<hr>
<br>
@foreach ($blogs as $blog)
    <h1 class="text-2xl font-bold mb-4">{{ $blog->blog_title }}</h1>
    <p class="text-gray-700 mb-6">{{ $blog->blog_content }}</p>
    <p class="text-gray-500">Written by <span class="font-bold">{{ $blog->user->name }}</span></p>
    <br>
    @auth
        <div class="flex">
            <a href="{{ route('blog.show', $blog->id) }}" class="btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"
                style="margin-right: 8px;">Edit</a>
            <form method="POST" action="{{ route('blog.destroy', $blog->id) }}"
                onsubmit="return confirm('Are you sure you want to delete this blog?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded"
                    style="margin-left: 8px;">DELETE</button>
            </form>
        </div>
        
    @endauth
    <hr>
@endforeach
{{ $blogs->links() }}
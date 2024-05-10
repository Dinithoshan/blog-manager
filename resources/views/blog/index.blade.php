@include('layouts.navi')

<div class="container mx-auto">
    <hr>
    <h1 class="text-4xl font-bold mb-4">Latest News</h1>
    <hr>
    <div class="my-8">
        @foreach ($blogs as $blog)
            <h1 class="text-2xl font-bold mb-4">{{ $blog->blog_title }}</h1>
            <p class="text-gray-700 mb-6">{{ $blog->blog_content }}</p>
            <p class="text-gray-500">Written by <span class="font-bold">{{ $blog->user->name }}</span></p>
            <br>
            @auth
                @can('unpublish')
                    <form method="POST" action="{{ route('blog.toggleUnpublish', $blog->id) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Unpublish</button>
                    </form>
                    @can('write-article')
                    <a href="{{ route('blog.edit', $blog->id) }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Edit</a>
                    @endcan
                @endcan
            @endauth
            <hr>
        @endforeach
    </div>
    {{ $blogs->links() }}
</div>

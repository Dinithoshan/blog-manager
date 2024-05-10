@include('layouts.navi')
<div class="container mx-auto">
    <div class="my-7">
        @foreach ($unpublished as $pending)
            <br>
            <form action="{{ route('blog.togglePublish', $pending->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <h1 class="text-3xl font-bold mb-4">{{ $pending->blog_title }}</h1>
                <p class="text-gray-700 mb-6">{{ $pending->blog_content }}</p>
                <p class="text-gray-500">Written by <span class="font-bold">{{ $pending->user->name }}</span></p>
                <br>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Publish</button>
            </form>
                @can('write-article')
                <a href="{{ route('blog.show', $pending->id) }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Edit</a>
                @endcan
                @can('delete-unpublised-article')
                <form method="POST" action="{{ route('blog.destroy', $pending->id) }}"
                    onsubmit="return confirm('Are you sure you want to delete this blog?')">
                    @csrf
                    @method('DELETE')
                    <br>
                    <button type="submit" class="btn bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">DELETE</button>
                </form>
                @endcan

            <br>
            <hr>
        @endforeach
        {{ $unpublished->links() }}
    </div>
</div>




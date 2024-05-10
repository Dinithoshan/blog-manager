{{-- This is a custom Navigation --}}

@vite(['resources/css/app.css', 'resources/js/app.js'])


<nav class="bg-gray-800 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <div>
            <a href="{{ route('blog.index') }}" class="text-white text-lg font-bold">Project Blogger</a>
        </div>
        <div>
            @auth
                <div class="text-white mr-5 text-lg">
                    Welcome, {{ auth()->user()->name }}
                </div>
                <ul class="flex space-x-4">
                    @can('write-article')
                        <li>
                            <a href="{{ route('blog.create') }}" class="text-white hover:text-gray-300">Create Blog</a>
                        </li>
                    @endcan
                    @can('publish')
                        <li>
                            <a href="{{ route('blog.unpublished') }}" class="text-white hover:text-gray-300">Unpublished Blogs</a>
                        </li>
                    @endcan

                    @can('manage-users')
                        <li>
                            <a href="{{ route('admin.index') }}" class="text-white hover:text-gray-300">Users</a>
                        </li>
                    @endcan
                    @can('view-article')
                        <li>
                            <a href="{{ route('blog.owned', auth()->id()) }}" class="text-white hover:text-gray-300">My
                                Blogs</a>
                        </li>
                    @endcan

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <li>
                            <button type="submit" class="text-white hover:text-gray-300">Log Out</button>
                        </li>
                    </form>
                </ul>
            @else
                <a href="{{ route('login') }}" class="text-white hover:text-gray-300">Login</a>
            @endauth
        </div>
    </div>
</nav>

{{-- @include('layouts.navi')


@if (@session('status'))
    <div class="alert alert-success">{{ session('status')}}</div>
@endif

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-4">User List</h1>
    <div class="overflow-x-auto">
        <a href="{{ route('admin.create')}}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create User</a>
        <table class="mt-8 min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" css="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created at</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated at</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">View Details</th> <!-- New column for the "Show" button -->
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->updated_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('admin.show', $user->id) }}" class="text-blue-500 hover:text-blue-700">Show</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> --}}
@include('layouts.navi')

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif


@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-4">User List</h1>
    <div class="overflow-x-auto">
        <a href="{{ route('admin.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create User</a>
        <table class="mt-8 min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created at</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated at</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">View Details</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @foreach ($user->roles as $role)
                            {{ $role->name }}
                            @if (!$loop->last)
                                , 
                            @endif
                        @endforeach
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->updated_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('admin.show', $user->id) }}" class="text-blue-500 hover:text-blue-700">Show</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

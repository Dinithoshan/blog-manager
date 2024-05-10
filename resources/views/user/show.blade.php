@include('layouts.navi')

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('admin.update', $user->id) }}" class="max-w-md mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
    @csrf
    @method('PUT')

    <div class="mb-4">
        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
        <input id="name" type="text" class="form-input w-full border border-gray-300 rounded py-2 px-3 leading-tight focus:outline-none focus:border-blue-500" name="name" value="{{ $user->name }}" required>
    </div>
    <div class="mb-4">
        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
        <input id="email" type="email" class="form-input w-full border border-gray-300 rounded py-2 px-3 leading-tight focus:outline-none focus:border-blue-500" name="email" value="{{ $user->email }}" required>
    </div>
    <div class="mb-4">
        <label for="created_at" class="block text-gray-700 text-sm font-bold mb-2">Created At</label>
        <input id="created_at" type="text" class="form-input w-full border border-gray-300 rounded py-2 px-3 leading-tight focus:outline-none focus:border-blue-500" value="{{ $user->created_at }}" readonly>
    </div>
    <div class="mb-6">
        <label for="updated_at" class="block text-gray-700 text-sm font-bold mb-2">Updated At</label>
        <input id="updated_at" type="text" class="form-input w-full border border-gray-300 rounded py-2 px-3 leading-tight focus:outline-none focus:border-blue-500" value="{{ $user->updated_at }}" readonly>
    </div>
        <div class="mb-4">
            <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role</label>
            <select name="role" id="role" class="form-select w-full border border-gray-300 rounded py-2 px-3 leading-tight focus:outline-none focus:border-blue-500">
                <option value="writer" selected>Writer</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
            </select>
        </div>
    <div class="flex items-center justify-between">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update</button>
    </div>
</form>
<form method="POST" action="{{ route('admin.destroy', $user->id) }}" class="max-w-md mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
    @csrf
    @method('DELETE')
    <div class="flex items-center justify-between">
        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Disable</button>
    </div>
</form>


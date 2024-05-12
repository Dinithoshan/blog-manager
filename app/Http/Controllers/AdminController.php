<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //validating if Authenticated user has permissions to perform action
        $admin = Auth::user();
        if (!$admin ->hasPermissionTo('manage-users')) {
            abort(403, 'Insuffiecient Permissions to perform action');
        }

        $users = User::all()->sortByDesc('created_at');
        return view('user.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $admin = Auth::user();
        if (!$admin ->hasPermissionTo('manage-users')) {
            abort(403, 'Insufficient Permissions to perform action');
        }
        return redirect()->route('register');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $admin = Auth::user();
        if (!$admin ->hasPermissionTo('manage-users')) {
            abort(403, 'Insuffiecient Permissions to perform action');
        }
        $user = User::find($id);
        return view('user.show', ['user' => $user]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        //Check if current user has privileges to make update users

        $currentUSer = Auth::user();

        if (!$currentUSer->hasPermissionTo('manage-users')) {
            abort(403, 'Insuffiecient Permissions to perform action');
        }

        //Validation of the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:writer,manager,admin'
        ]);

        //Finding the user by ID
        $user = User::findOrFail($id);

        ///Finding Existing role in the database and removing it
        $existingRole = $user->roles()->first();

        // If user has admin role and there's only one admin user, don't remove admin role
        if ($existingRole && $existingRole->name === 'admin' && User::role('admin')->count() === 1) {
            return redirect()->back()->with('error', 'Cannot remove admin role from the last admin user.');
        }

        //Remove the existing role
        if ($existingRole) {
            $user->removeRole($existingRole);
        }

        //Updating the user found by ID
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        //Assigning the new Role
        $user->assignRole($request->role);

        return to_route('admin.index')->with('success', 'User Updated Successfully!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Authentication validation of permissions
        $admin = Auth::user();
        if (!$admin ->hasPermissionTo('manage-users')) {
            abort(403, 'Insuffiecient Permissions to perform action');
        }
        //Finding user by id
        $user = User::find($id);
        //Getting is_published count to check for any published blogs
        $publishedBlogs= Blog::where('user_id', $user->id)->where('is_published', true)->count();


        //Check if User is the last admin user
        $isAdminRole = $user->hasRole('admin') && User::role('admin')->count() === 1;


        // If user has admin role and there's only one admin user, don't remove admin role
        if ($isAdminRole) {
            return redirect()->back()->with('error', 'Cannot remove admin role from the last admin user.');
        }
        //set acount to not active if account contains published roles
        elseif ($publishedBlogs > 0){
            $user->active = false;
            $user->save();
            return to_route('admin.index')->with('success', 'User was disabled Successfully!');
        } 
        //delete all unpublished blogs and delete account
        else {
            Blog::where('user_id', $user->id)->delete();
            $user->delete();
            return to_route('admin.index')->with('success', 'User was deleted Successfully!');
        }
    }
}

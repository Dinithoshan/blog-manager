<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Return blogs and their respective authors
        $blogs = Blog::with('user')->where('is_published', true)->orderBy('created_at', 'desc')->paginate(15);
        return view('blog.index', ['blogs' => $blogs]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        //Check for permissions to access the resource and return the create page.
        $currentUser = Auth::user();

        if (!$currentUser->hasPermissionTo('write-article')) {
            abort(401);
        } else {
            return view('blog.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->hasPermissionTo('write-article')) {
            abort(403, 'Action not Permitted');
        } else {
            //stores currently authenticated user_id
            $user_id = auth()->id();
            //request validation
            $data = $request->validate([
                'blog_title' => ['required', 'string'],
                'blog_content' => ['required', 'string'],
                'user_id' => [
                    Rule::in([$user_id])
                ]
            ]);
            $blog = Blog::create($data);
            return to_route('blog.owned', $user_id)->with('message', 'Blog was created Successfully');
        }
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        return view('blog.edit', ['blog' => $blog]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $currentUser = Auth::user();
        $blog = Blog::findOrFail($id);

        //validate Ownership and admin privileges
        if (!$currentUser->hasPermissionTo('edit-unpublished-article') && !$currentUser->hasRole('admin') && $blog->user_id !== $currentUser->id) {
            abort(401, 'Insufficient Permissions');
        }
        //Retrieve the blog

        return view('blog.show', ['blog' => $blog]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        //Checking if the authenticated user has permissions    
        $currentUser = Auth::user();

        if (!$currentUser->hasPermissionTo('edit-unpublished-article')) {
            abort(401);
        } else {
            //finding the blog by the id
            $blog = Blog::findOrFail($id);

            //Make sure that the blog is not published before it is updated
            if ($blog->is_published) {
                abort(403, 'cannot update a published blog');
            }

            //validate ownership of the blog
            if ($blog->user_id !== $currentUser->id) {
                abort(403, 'Cannot update blog that doesn\'t belong to you');
            } elseif (!$currentUser->hasRole('admin'))
                ;
            else {
                //Validating the data
                $request->validate([
                    'blog_title' => 'required|string|max:255',
                    'blog_content' => 'required|string',
                ]);

                //updating the blog
                $blog->update([
                    'blog_title' => $request->input('blog_title'),
                    'blog_content' => $request->input('blog_content'),
                ]);

                //Redirect to the owned blog page with a success message
                return to_route('blog.owned', auth()->id())->with('success', 'Blog updated successfully');
            }


        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //check if user has permission to delete unpublished article
        $currentUser = Auth::user();

        if (!$currentUser->hasPermissionTo('delete-unpublised-article')) {
            abort(401);
        }

        //Finding the blog by ID 
        $blog = Blog::findOrFail($id);
        if ($blog->user_id !== $currentUser->id) {
            abort(403);
        }
        //Check whether the blog is published and delete if it is not
        if (!$blog->is_published) {
            $blog->delete();
            return redirect()->route('blog.owned', $currentUser->id)->with('success', 'Blog Deleted Successfully');
        } else {
            abort(403, 'Cannot delete Published blog');
        }
    }

    public function unpublished()
    {
        //Validate the user accessing the unpublished blogs
        $currentUser = Auth::user();
        if (!$currentUser->hasPermissionTo('unpublish')) {
            abort(403, 'Not Authorized to View this page');
        }
        $unpublised = Blog::with('user')->where('is_published', false)->orderBy('created_at', 'desc')->paginate(15);
        return view('blog.unpublished', ['unpublished' => $unpublised]);
    }

    public function togglePublish($blog_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasPermissionTo('publish')) {
            abort(401, 'User not authorized to perform action');
        }
        try {
            $blog = Blog::findOrFail($blog_id);
            $blog->update(['is_published' => true]);
            return to_route('blog.unpublished')->with('message', 'Blog has been published successfully');
        } catch (ModelNotFoundException $e) {
            return to_route('blog.unpublished')->with('message', 'Error blog on found');
        }
    }

    public function toggleUnpublish($blog_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasPermissionTo('unpublish')) {
            abort(401, 'User not authorized to perform action');
        }
        try {
            $blog = Blog::findOrFail($blog_id);
            $blog->update(['is_published' => false]);
            return to_route('blog.index')->with('message', 'Blog has been unpublished successfully');
        } catch (ModelNotFoundException $e) {
            return to_route('blog.index')->with('message', 'Error blog on found');
        }
    }

    public function owned($user_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasPermissionTo('view-article')) {
            abort(403);
        }
        $blogs = Blog::with('user')->where("user_id", request()->user()->id)->orderBy("created_at", "desc")->paginate(10);
        return view("blog.owned", ['blogs' => $blogs]);
    }
}

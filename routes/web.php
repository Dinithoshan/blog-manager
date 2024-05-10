<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublishController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

//Public Route
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');

//User will be directed to public blog page when visiting the site
Route::get('/', function () {
    return redirect()->route('blog.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('blog.index');
})->middleware(['auth', 'verified'])->name('dashboard');



require __DIR__ . '/auth.php';
Route::middleware('active')->group(function () {



    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });



    




    //Routes for administrators (privileged accounts)
    Route::middleware('permission:manage-users')->group(function () {
        Route::resource('admin', AdminController::class);
        Route::get('/blog/edit/{id}', [BlogController::class, 'edit'])->name('blog.edit');
    });



    //Routes for blogs

    //Grouping the routes for manager users
    Route::middleware('permission:publish')->group(function () {
        Route::get('/blog/unpublished', [BlogController::class, 'unpublished'])->name('blog.unpublished');
        Route::put('/blog/{id}/publish', [BlogController::class, 'togglePublish'])->where('blog_id', '[0-9]+')->name('blog.togglePublish');
        Route::put('/blog/{id}/unpublish', [BlogController::class, 'toggleUnpublish'])->where('blog_id', '[0-9]+')->name('blog.toggleUnpublish');
        Route::get('/blog/unpublished', [BlogController::class, 'unpublished'])->name('blog.unpublished');
    });


    //Grouping routes for writers""
//Permissions are set seperately to all crud operations("write-article", "edit-unpublished-article", "delete-unpublished-article")
    Route::middleware('permission:write-article,view-article')->group(function () {
        Route::get('/blog/myblogs/{id}', [BlogController::class, 'owned'])->where('user_id', '[0-9]+')->name('blog.owned');
        Route::get('/blog/create', [BlogController::class, 'create'])->name('blog.create');
        Route::post('/blog', [BlogController::class, 'store'])->name('blog.store');
        Route::get('/blog/{blog}', [BlogController::class, 'show'])->name('blog.show');//Editing also ultilizes the show route
        Route::put('/blog/{blog}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/blog/{blog}', [BlogController::class, 'destroy'])->name('blog.destroy');
    });
});
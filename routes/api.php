<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', function () {
    return ['ping' => true];
});

Route::get('/401', [AuthController::class, 'unauthorized'])->name('unauthorized');

Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/user', [UserController::class, 'create'])->name('user.create');

Route::middleware(['auth:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('refresh');

    Route::get('/feed', [FeedController::class, 'feed'])->name('feed');
    Route::post('/feed', [FeedController::class, 'create'])->name('feed.create');
    Route::get('/user/feed', [FeedController::class, 'userFeed'])->name('user.feed');
    Route::get('/user/{id}/feed', [FeedController::class, 'userFeed'])->name('user.feed.id');
    Route::get('/user/{id}/photos', [FeedController::class, 'userPhotos'])->name('user.photos');

    Route::get('/user', [UserController::class, 'read'])->name('user.read');
    Route::put('/user', [UserController::class, 'update'])->name('user.update');
    Route::post('/user/avatar', [UserController::class, 'updateAvatar'])->name('user.update.avatar');
    Route::post('/user/cover', [UserController::class, 'updateCover'])->name('user.update.cover');
    Route::get('/user/{id}', [UserController::class, 'read'])->name('user.read.id');
    Route::post('/user/{id}/follow', [UserController::class, 'follow'])->name('user.follow');
    Route::get('/user/{id}/following', [UserController::class, 'following'])->name('user.following');
    Route::get('/user/{id}/followers', [UserController::class, 'followers'])->name('user.followers');

    Route::post('/post/{id}/like', [PostController::class, 'like'])->name('post.like');
    Route::post('/post/{id}/comment', [PostController::class, 'comment'])->name('post.comment');

    Route::get('/search', [SearchController::class, 'search'])->name('search');

});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifySanctum;
use App\Http\Controllers\AppController;
use App\Http\Controllers\UserController;

Route::prefix('app')->name('app.')->group(function () {
	Route::get('lastPlayer', [AppController::class, 'getLastPlayer']);
	Route::post('recentApp', [AppController::class, 'getRecentApp']);

	// Authenticate User
	Route::prefix('auth')->name('auth.')->group(function () {
		Route::post('tokenApp', [UserController::class, 'getTokenApp']);
		Route::post('codePasswordRestore', [UserController::class, 'codePasswordRestore']);
		Route::post('passwordRestore', [UserController::class, 'passwordRestore']);
		Route::post('registerApp', [UserController::class, 'getRegister']);
		Route::middleware('auth:sanctum')->group(function () {
			Route::get('logout', [UserController::class, 'logoutUser']);
			Route::get('user-data', [UserController::class, 'getUserData']);
		});
		Route::middleware([VerifySanctum::class])->group(function () {
			Route::post('update', [UserController::class, 'updateProfile']);
			Route::prefix('favorite')->group(function () {
				Route::post('add', [UserController::class, 'addAnimeFavorite']);
				Route::post('delete', [UserController::class, 'deleteAnimeFavorite']);
				Route::post('list', [UserController::class, 'listAnimeFavorite']);
			});
			Route::prefix('view')->group(function () {
				Route::post('add', [UserController::class, 'addAnimeView']);
				Route::post('delete', [UserController::class, 'deleteAnimeView']);
				Route::post('list', [UserController::class, 'listAnimeView']);
			});	
			Route::prefix('watching')->group(function () {
				Route::post('add', [UserController::class, 'addAnimeWatching']);
				Route::post('delete', [UserController::class, 'deleteAnimeWatching']);
				Route::post('list', [UserController::class, 'listAnimeWatching']);
			});	
			Route::prefix('watchlater')->group(function () {
				Route::post('add', [UserController::class, 'addAnimeWatchlater']);
				Route::post('delete', [UserController::class, 'deleteAnimeWatchlater']);
				Route::post('list', [UserController::class, 'listAnimeWatchlater']);
			});				
		});
	});
	Route::get('view-anime/{episode_id}', [AppController::class, 'setViewsAnime']);
	Route::get('view-animes/{id}', [AppController::class, 'setViewsAnimes']);
	Route::get('report-player/{player_id}', [AppController::class, 'addReportPlayer']);
});

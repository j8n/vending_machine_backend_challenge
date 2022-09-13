<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;


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

Route::get('/', function(){
    return 'Index';
});

// Store user route
Route::post('users', [UserController::class, 'store'])->name('users.store');

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Users
    Route::post('users/{id}/deposit', [UserController::class, 'deposit'])->name('users.deposit');
    Route::post('users/{id}/buy', [UserController::class, 'buy'])->name('users.buy');
    Route::post('users/{id}/reset', [UserController::class, 'reset'])->name('users.reset');
    Route::apiResource('users', UserController::class)->except([
        'store'
    ]);
    
    // Products
    Route::apiResource('products', ProductController::class);
});

Route::get('/test', function(){
    
    // return route('users.buy', 1);

    $user = App\Models\User::create([
        'username' => 'john buyer',
        'password' => '123123123',
        'role_id' => App\Models\Role::BUYER
    ]);

    $user->depositAmount(100);
    $user->depositAmount(100);
    $user->depositAmount(20);
    $user->depositAmount(10);

    return $user->calculateChange();

    // return new App\Http\Resources\UserResource($user);

});

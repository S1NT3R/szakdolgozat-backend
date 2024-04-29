<?php

use App\Http\Controllers\Api\AdviceController;
use App\Http\Controllers\Api\ChoresController;
use App\Http\Controllers\Api\ClothesController;
use App\Http\Controllers\Api\TodosController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
Route::post('advice/', [AdviceController::class, 'getAdvice']);
//});

Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/register', [UserController::class, 'register']);
Route::get('advice/all', [AdviceController::class, 'getAllAdvicesByType']);

Route::group([
    'middleware' => ['jwt.auth']
], function () {
    Route::get('user/logout', [UserController::class, 'logout']);
    Route::get('user/', [UserController::class, 'getUser']);
    Route::post('user/upload-avatar', [UserController::class, 'uploadAvatar']);
    Route::get('user/delete-avatar', [UserController::class, 'deleteAvatar']);
    Route::get('user/update-streak', [UserController::class, 'updateStreak']);
    Route::post('user/update', [UserController::class, 'updateUserDetails']);
    Route::post('user/change-password', [UserController::class, 'changePassword']);
    Route::post('user/delete', [UserController::class, 'deleteUser']);

    Route::post('todo/add', [TodosController::class, 'addTodo']);
    Route::post('todo/', [TodosController::class, 'getTodos']);
    Route::post('todo/toggle', [TodosController::class, 'toggleTodo']);
    Route::post('todo/delete', [TodosController::class, 'deleteTodo']);
    Route::post('todo/update', [TodosController::class, 'updateTodo']);

    Route::post('chore/add', [ChoresController::class, 'addChore']);
    Route::post('chore/', [ChoresController::class, 'getChores']);
    Route::post('chore/toggle', [ChoresController::class, 'toggleChore']);
    Route::post('chore/delete', [ChoresController::class, 'deleteChore']);
    Route::post('chore/update', [ChoresController::class, 'updateChore']);


    Route::post('advice/add', [AdviceController::class, 'addAdvice']);
    Route::post('advice/delete', [AdviceController::class, 'deleteAdvice']);

    Route::post('clothe/add', [ClothesController::class, 'addClothe']);
    Route::get('clothe/', [ClothesController::class, 'getClothes']);
    Route::post('clothe/delete', [ClothesController::class, 'deleteClothe']);
    Route::post('clothe/toggle', [ClothesController::class, 'toggleClothe']);
    Route::post('clothe/update', [ClothesController::class, 'updateClothe']);
    Route::get('clothe/generate', [ClothesController::class, 'generateLaundry']);

});

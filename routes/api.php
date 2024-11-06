<?php

use App\Http\Controllers\ChatroomController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

Route::get('/chatrooms', [ChatroomController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chatrooms/my', [ChatroomController::class, 'myChatrooms']);
    Route::post('/chatrooms', [ChatroomController::class, 'store']);
    Route::post('/chatrooms/{chatroom}/leave', [ChatroomController::class, 'leave']);
    Route::post('/chatrooms/{chatroom}/enter', [ChatroomController::class, 'enter']);

    Route::get('/chatrooms/{chatroom}/messages', [MessageController::class, 'index']);
    Route::post('/chatrooms/{chatroom}/messages', [MessageController::class, 'sendMessage']);
});

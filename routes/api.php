<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CreatePostController;
use App\Http\Controllers\ReadPostController;
use App\Http\Controllers\DeletePostController;
use App\Http\Controllers\UpdatePostController;
use App\Http\Controllers\UserInfoController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RequestController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
    

Route::group(['middleware'=>'api','perfix'=>'auth'],function($router){
    
    Route::post('/register',[UserController::class,'register']);
    Route::post('/login',[UserController::class,'login']);
    Route::get('/welcome/{email}/{verify_email}',[UserController::class,'welcome']);
    Route::post('/logout',[UserController::class,'logout']);
    Route::post('/readInfo',[UserController::class,'readInfo']);
    Route::post('/readComment',[UserController::class,'readComment']);
    Route::post('/seeAllFriend',[UserController::class,'seeAllFriend']);
});

Route::group(['middleware'=>'customauth'],function($router)
{
    Route::post('/create_post',[CreatePostController::class,'post']);
    Route::post('/read_post',[ReadPostController::class,'read']);
    Route::post('/delete_post',[DeletePostController::class,'delete']);
    Route::post('/update_post',[UpdatePostController ::class,'update']);
    
    Route::post('/showprofile',[UserInfoController::class,'showprofile']); 

    Route::post('/comment',[CommentController::class,'createComment']);
    Route::post('/updateComment',[CommentController::class,'updateComment']);
    Route::post('/deleteComment',[CommentController::class,'deleteComment']);


    Route::post('/addFriend',[RequestController::class,'addFriend']);
    Route::post('/deleteFriend',[RequestController::class,'deleteFriend']);
});



<?php
namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\post;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Service\DatabaseConnection;


class CreatePostController extends Controller
{
    public function post(PostRequest $request)
    {   
      $request->validated();
      $key=$request->access_token;
      $connection=new DatabaseConnection();
      $get=$connection->createconnection("users")->findOne([
            'remember_token' =>$key
        ]); 
      if($get==null)
      {
        return response()->json(['message'=>'Login Again']);
      }
      else
      {
          $user_id=$get->_id;
          $path = $request->file('file')->store('post');
          $file = $path;
          $access=$request->access;
          $get=$connection->createconnection("posts")->insertOne([
          'user_id'=>$user_id,
          'file'=>$file,
          'access'=>$access
          ]);
           return response()->json(['message'=>'Posted Successfully']);
       }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\LoginAccessRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\post;
use Illuminate\Support\Facades\DB;
use App\Service\DatabaseConnection;

class UpdatePostController extends Controller
{
    public function update(LoginAccessRequest $request)
    {

        $request->validated();
        $key=$request->access_token;
        $post_id=$request->post_id;
        $connection=new DatabaseConnection();
        $get=$connection->createconnection("users")->findOne([
              'remember_token' =>$key
          ]);       
        if($get==null)
        {
            return response()->json(["messsage" => "Login Again"]);
        }
        else
        {
            $user_id=$get->_id;
            $id = new \MongoDB\BSON\ObjectId($post_id);
            $data=$connection->createconnection("posts")->findOne(
                ['_id' => $id],
                );
            $user= $data->user_id;
            if($user_id == $user){
                $connection->createconnection("posts")->updateOne(
                    [ '_id' => $id ],
                    [ '$set' => [ 'file' => $request->file ,'access' => $request->access]]);
                return response()->json(["messsage" => "Updated"]);
            }
            else
            {
                return response()->json(["messsage" => "You donot have access to this post"]);
            }
        }   
    }    
}


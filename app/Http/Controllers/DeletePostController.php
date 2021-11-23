<?php

namespace App\Http\Controllers;

use App\Models\post;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Service\DatabaseConnection;
use App\Http\Requests\LoginAccessRequest;

class DeletePostController extends Controller
{

    public function delete(LoginAccessRequest $request)
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
                $connection->createconnection("posts")->deleteOne(['_id' => $id]);
                return response()->json(["messsage" => "Deleted"]);
            }
            else
            {
                return response()->json(["messsage" => "You donot have access to this post"]);
            }

        }

    }
}

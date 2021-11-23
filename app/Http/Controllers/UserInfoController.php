<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginAccessRequest;
use App\Models\post;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Service\DatabaseConnection;

class UserInfoController extends Controller
{
    public function showprofile(LoginAccessRequest $request)//function return the profile information of the user
    {
        $request->validated();
        $key=$request->access_token;
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
            $data = $connection->createconnection("users")->find(['_id' => $user_id]);
            $objects = json_decode(json_encode($data->toArray(),true));
            $array=json_decode(json_encode($objects),true);
            return response([$array]);
            PCNTL_ECHILD;
        }
    }

    
}

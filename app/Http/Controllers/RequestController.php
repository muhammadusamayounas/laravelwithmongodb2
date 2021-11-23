<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\FriendRequest;

class RequestController extends Controller
{  
    function addFriend(FriendRequest $request)
    {
            $request->validated();
            
            $user = new Friend;
            $access_token = $user->token = $request->input('access_token');
            $name = $user->name = $request->input('name');
           
            if(!empty($access_token))
            {
                $requestsender = DB::table('users')->where(['remember_token' => $access_token])->get();
                $requestreceiver = DB::table('users')->where(['name' => $name])->get();
                $wordcount1 = count($requestsender);//person is making request
                $wordcount2 = count($requestreceiver);//person which you are adding as friend   


                $id1 = $requestsender[0]->id;
                $sendername=$requestsender[0]->name;
  
                $id2 = $requestreceiver[0]->id;
                $requestreceiver_verify = $requestreceiver[0]->email_verified_at;

                $check = DB::table('friends')->where(['sender_id' => $id1, 'receiver_id' => $id2])->get();
                $sql = count($check);

                if($sql == 0)
                {
                    if($wordcount1 > 0 && $wordcount2 > 0)
                    {
                        if(!empty($requestreceiver_verify))
                        {
                            if($id1 != $id2)
                            {   
                                $values = array('sender_id' => $id1,'sender_name'=>$sendername,'receiver_id' => $id2,'receiver_name'=>$name);
                                DB::table('friends')->insert($values);
                
                                return response(['Message' => 'Congrats '.$name.' is your friend']);
                            }
                            else
                            {
                                return response(['Message' => 'You cannot add yourself as a friend.']);   
                            }                            
                        }       
                        else
                        {
                            return response(['Message' => 'Account not found']);                           
                        } 
                    }
                    else
                    {
                        return response(['Message' => 'Error']);
                    }
                }
                else
                {
                    return response(['Message' => 'Alread your Friend you cannot send him a friend request']);
                }
            }
            else
            {
                return response(['Message' => 'Login Account Again']);
            }    
    }

    public function deleteFriend(FriendRequest $request)
    {
          $request->validated();
          $user = new Friend;
          $access_token = $user->token = $request->input('access_token');
          $name = $user->name = $request->input('name');     
          $getid = DB::table('users')->where(['remember_token' => $access_token])->get();
          $id = $getid[0]->id;    
          if(count($getid)>0)
           {
              DB::table('friends')->where(['receiver_name'=>$name,'sender_id'=>$id])->delete();
              return response(['Message' => 'Friend Deleted']);
            }
            else
            {
                return response(['Message' => 'Login Account Again']);
            }

    }
}


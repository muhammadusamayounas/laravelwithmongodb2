<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateandDeleteCommentRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\testmail;
use App\Service\DatabaseConnection;



class CommentController extends Controller
{
    public function createComment(CommentRequest $request)
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
        return response()->json(['message'=>'Login Again']);
      }
      else
      {
          $id = new \MongoDB\BSON\ObjectId($post_id);
          $path = $request->file('file')->store('post');
          $comment=$request->comment;
          $comment = array(
            "_id" => new \MongoDB\BSON\ObjectId(),
            "user_id" => $get["_id"],
            "file" => $path,
            "comment" => $comment,
        );

        $connection->createconnection("posts")->updateOne(
          ["_id" => $id],
          ['$push'=>["Comments" => $comment]]
        );

        $get_user_id=$connection->createconnection("posts")->findOne([
          '_id' =>$id]
        ); 
        $user_id=$get_user_id->user_id;

        $get_user_email=$connection->createconnection("users")->findOne([
          '_id' =>$user_id]
        ); 
        $user_email=$get_user_email->email;
        $details=
        [
            'title'=> 'Comment Notification',
            'body'=> 'You got a comment',
        ]; 
         Mail::to($user_email)->send(new testmail($details));
        return response()->json(['message'=>'Comment Added']);
       }    
   }
}

//    public function updateComment(UpdateandDeleteCommentRequest $request)
//    {
//        $request->validated();
//        $key=$request->access_token;
//        $comment=$request->comment;
//        $comment_id=$request->comment_id;
//        $data=DB::table('users')->where('remember_token',$key)->get();
//        if(count($data)>0)
//        {
//          $id=$data[0]->id;
//          $path = $request->file('file')->store('post');
//          $updateDetails = [
//           'user_id' => $id,
//           'file' => $path,
//           'comment'=> $comment
//           ];
//          if(DB::table('comments')->where(['id'=> $comment_id,'user_id'=> $id])->update($updateDetails)==1)
//          {
//             return response()->json(["messsage" => "Comment Updated successfully"]);
//          }
//          else
//          {
//           return response()->json(["messsage" => "You Are Not Allowed To Delete Others Comment"]);
//          }
//        }
//        else
//        {
//         return response()->json(["messsage" => "Login Again"]);
//        }
//    }

//    public function deleteComment(UpdateandDeleteCommentRequest $request)
//    {
//        $request->validated();
//        $key=$request->access_token;
//        $comment_id=$request->comment_id;
//        $data=DB::table('users')->where('remember_token',$key)->get();
//        if(count($data)>0)
//        {
//          $id=$data[0]->id;
//          echo $comment_id;
//          if(DB::table('comments')->where(['id'=> $comment_id,'user_id'=> $id])->delete() == 1)
//          {
//            return response()->json(["messsage" => "Comment Deleted Successfuly"]);
//          }
//          else
//          {
//             return response()->json(["messsage" => "You Are Not Allowed To Delete Others Comment"]);
//          }
//        }
//        else
//        {
//         return response()->json(["messsage" => "You Are Not Login"]);
//        }
//     }
// }
   







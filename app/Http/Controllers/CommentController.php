<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateandDeleteCommentRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\testmail;



class CommentController extends Controller
{
    public function createComment(CommentRequest $request)
    {
      $request->validated();
      $key=$request->access_token;
      $comment=$request->comment; 
      $post_id=$request->post_id;
      $data = DB::table('users')->where('remember_token', $key)->get();
      $wordCount = count($data);
      if($wordCount > 0)
      {
        $id=$data[0]->id;//geting user id
        $name=$data[0]->name;
        $comments = new Comment;
        $path = $request->file('file')->store('post');
        $comments->comment=$comment;
        $comments->post_id=$post_id;
        $comments->user_id=$id;
        $comments->file = $path;
        $comments->save();
        $getid = DB::table('comments')->where('post_id', $post_id)->get();
        $user_id=$getid[0]->user_id;
        $getemail = DB::table('users')->where('id', $user_id)->get();
        $getemail=$getemail[0]->email;
        $details=[
          'title'=> 'Comment Notification',
          'body'=> 'You got a comment on your post by '.$name.' and comment from the user is '.$comment.'',
      ]; 
      Mail::to($getemail)->send(new testmail($details));
        return response()->json(['message'=>'Commented added']);

      } 
   }

   public function updateComment(UpdateandDeleteCommentRequest $request)
   {
       $request->validated();
       $key=$request->access_token;
       $comment=$request->comment;
       $comment_id=$request->comment_id;
       $data=DB::table('users')->where('remember_token',$key)->get();
       if(count($data)>0)
       {
         $id=$data[0]->id;
         $path = $request->file('file')->store('post');
         $updateDetails = [
          'user_id' => $id,
          'file' => $path,
          'comment'=> $comment
          ];
         if(DB::table('comments')->where(['id'=> $comment_id,'user_id'=> $id])->update($updateDetails)==1)
         {
            return response()->json(["messsage" => "Comment Updated successfully"]);
         }
         else
         {
          return response()->json(["messsage" => "You Are Not Allowed To Delete Others Comment"]);
         }
       }
       else
       {
        return response()->json(["messsage" => "Login Again"]);
       }
   }

   public function deleteComment(UpdateandDeleteCommentRequest $request)
   {
       $request->validated();
       $key=$request->access_token;
       $comment_id=$request->comment_id;
       $data=DB::table('users')->where('remember_token',$key)->get();
       if(count($data)>0)
       {
         $id=$data[0]->id;
         echo $comment_id;
         if(DB::table('comments')->where(['id'=> $comment_id,'user_id'=> $id])->delete() == 1)
         {
           return response()->json(["messsage" => "Comment Deleted Successfuly"]);
         }
         else
         {
            return response()->json(["messsage" => "You Are Not Allowed To Delete Others Comment"]);
         }
       }
       else
       {
        return response()->json(["messsage" => "You Are Not Login"]);
       }
    }
}
   



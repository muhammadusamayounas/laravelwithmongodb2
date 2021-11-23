<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\testmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\LoginRequest;
use App\Service\DatabaseConnection;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginAccessRequest;
use App\Http\Controllers\TokenController;



class UserController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth:api',['except'=>['login','register','welcome','logout','readInfo','readComment','seeAllFriend']]) ;
    }
    public function register(SignUpRequest $request)//sign_up
    {
        $connection=new DatabaseConnection();
        $table='users';
        $connect=$connection->createconnection($table);
        $request->validated();
        $name=$request->name;
        $email=$request->email;
        $password=Hash::make($request->password);
        $gender=$request->gender;
        $status=0;
        $token =$token = rand(100,1000);
        $mail=$request->email;
        $insert=$connect->insertOne([
            'name'=>$name,
            'email'=>$email,
            'password'=>$password,
            'gender'=>$gender,
            'status'=>$status,
            'token'=>$token,
            'email_verified'=>FALSE
        ]);
        $this->sendmail($mail,$token);
        return response()->json(["message"=>"Please verify your account"]);  
    }
    public function sendmail($email,$user_token)
    { 
        $details=[
            'title'=>'You are successfully sign up to our SocialApp',
             'body'=>'http://127.0.0.1:8000/api/welcome'.'/'.$email.'/'.$user_token];

        Mail::to($email)->send(new testmail($details));
        return "Email Send";
    }

    public function welcome($email, $verify_email)//email verify
    {
        $connection=new DatabaseConnection();
        $table='users';
        $connect=$connection->createconnection($table);

        $get= $connect->findOne(
        [
            'email' => $email,
            'token' => (int)$verify_email,
        ]);
        if(!empty($get))
        {
            $connect->updateMany(array("email"=>$email),
            array('$set'=>array('email_verified'=>1,'email_verified_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'))));           
            return response(['Message'=>'Your Email has been Verified']);
        }
        else
        {
            return response(['Message' => 'Account doesnot present']);
        }

    }

    public function login(LoginRequest $request)//login 

     {
        $connection=new DatabaseConnection();
        $connect=$connection->createconnection("users");
        $email=$request->email;
        $password=$request->password;
        $get=$connect->findOne([
            'email' => $email
        ]);
        if($get->email_verified == 1){
            $getpassword = $get->password;
            if (Hash::check($password, $getpassword)) {
                $jwt=new TokenController($email);
                $token=$jwt->Generate_jwt(); 
                $connect->updateOne(
                    [ 'email' => $email ],
                    [ '$set' => [ 'status' => 1 ,'remember_token' => $token]]
                 );
                return response()->json(['access_token'=>$token , 'message'=> 'Login']);
              }
            else
            {
                return response()->json(['message'=> 'Password doesnot match']);
            }
        }
        else
        {
            return response()->json(['message'=> 'Please verify your account']);
        }   
    } 
     
    public function logout(LoginAccessRequest $request)
    {
        
      $request->validated();
      $key=$request->access_token;
      $connection=new DatabaseConnection();
      $get=$connection->createconnection("users")->findOne([
            'remember_token' => $key
        ]);
      if($get==NULL)
        {
            return response()->json(['message'=>'Kindly Login First']);
        }
        else
        {
            $connection->createconnection("users")->updateOne(
                [ 'remember_token' => $key ],
                [ '$set' => [ 'status' => 0 ,'remember_token' => NULL]]
            );
            return response()->json(['message'=>'Logout']);
        }
    }

    // function forgetPassword(ForgetPasswordRequest $request)
    // {

    //     $request->validated();
    //     $user = new User;
    //     $getmail = $user->email = $request->input('email');
    //     $data = DB::table('users')->where('email', $getmail)->get();
        
    //     if(count($data) > 0)
    //     {
    //         foreach ($data as $key)
    //         {
    //             $verfiy =$key->email_verified_at;
    //         }
    //         if(!empty($verfiy))
    //         {
    //             $verification_code=rand(1000,9999);
    //             DB::table('users')->where('email', $getmail)->update(['verify_token'=> $verification_code]);
    //             return response($this->sendNewMail($getmail,$verification_code));
    //         }
    //         else{
    //             return response(['Message'=>'Account is not verified']);
    //         }
    //     }
    //     else{
    //         return response(['Message'=>'Email doesnot match Please re enter the email address']);
    //     }
    // }
    // function sendNewMail($getmail,$verification_code)
    // {
    //     $details=[
    //         'title'=> 'Forget Password Verification',
    //         'body'=> 'Your OTP is '. $verification_code . ' Please copy and paste the change Password Api'
    //     ]; 
    //     Mail::to($getmail)->send(new testmail($details));
    //     return response(['Message' => 'An OTP has been sent to '.$getmail.' , Please verify and proceed further.']);
    // }
    // function userChangePassword(ChangePasswordRequest $request)
    // {
    //     $request->validated();
    //     $user = new User;
    //     $getmail = $user->email = $request->input('email');
    //     $verification_code= $user->verification_code= $request->input('verification_code');
    //     $password=bcrypt($request->input('password'));
    //     $data = DB::table('users')->where('email', $getmail)->get();
    //     $num = count($data);
        
    //     if($num > 0)
    //     {
    //         foreach ($data as $key)
    //         {
    //             $getcode=$key->verify_token;
    //         }
    //         if($getcode==$verification_code)
    //         {
    //             DB::table('users')->where('email', $getmail)->update(['password'=> $password]);
    //             return response(['Message'=>'Your Password has been updated']);
    //         }
    //         else{
    //             return response(['Message'=>'Otp Does Not Match.']);
    //         }
    //     }
    //     else{
    //         return response(['Message'=>'Please Enter Valid Mail.']); 
    //     }
    // }
    // public function readInfo(LoginAccessRequest $request)//funtion will return the user information and posts by that user
    // {
    //     $request->validated();
    //     $key=$request->access_token;
    //     $data = DB::table('users')->where('remember_token', $key)->get();
    //     $wordCount = count($data);
    //     if($wordCount > 0)
    //     {
    //         $userid=$data[0]->id;
    //         $sql=User::with("allUserPost")->where('id',$userid)->get();
    //         return response(['message'=>$sql]);
    //     }
    //     else
    //     {
    //       return response(['message'=>'Token Error Please Login Again']);
    //   }
    // }
   
    //  public function readComment(LoginAccessRequest $request)//funtion will return the user information and posts by that user and comments on that post
    // {
    //     $request->validated();
    //     $key=$request->access_token;
    //     $data = DB::table('users')->where('remember_token', $key)->get();
    //     $wordCount = count($data);
    //     if($wordCount > 0)
    //     {
    //         $userid=$data[0]->id;
    //         $sql=User::with("allUserPost","getComments")->where('id',$userid)->get();
    //         return response(['message'=>$sql]);
    //     }
    //     else
    //     {
    //       return response(['message'=>'Token Error Please Login Again']);
    //   } 
    // }
    // public function seeAllfriend(LoginAccessRequest $request)//funtion will return the user information and posts by that user
    // {
    //     $request->validated();
    //     $key=$request->access_token;
    //     $data = DB::table('users')->where('remember_token', $key)->get();
    //     $wordCount = count($data);
    //     if($wordCount > 0)
    //     {
    //         $userid=$data[0]->id;
    //         $sql=User::with("allUserFriend")->where('id',$userid)->get();
    //         return response(['message'=>$sql]);
    //     }
    //     else
    //     {
    //       return response(['message'=>'Token Error Please Login Again']);
    //   }
    // }
}

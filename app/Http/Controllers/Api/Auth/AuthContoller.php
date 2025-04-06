<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\S3\UploadFileRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use App\Models\Image;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Http;



class AuthContoller extends Controller
{
    private $uploadFileRepository;

    public function __construct(UploadFileRepositoryInterface $UploadFileRepository)
    {
        $this->uploadFileRepository = $UploadFileRepository;

    }
    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => ['These credentials do not match our records.']
                ], 404);
            }
            $token = $user->createToken('my-app-token')->plainTextToken;
            $response = [
                'user' => [
                    'id'=>$user->id,
                    'name'=>$user->name,
                    'email'=>$user->email,
                    'phone_number'=>$user->phone_number,
                    'governorate_id'=>($user->city_id != null)?$user->city->governorate_id:0,
                    'image'=>($user->profile != null )?"https://chat-broker-api.azurewebsites.net/images/".$user->profile:null,
                    'city_id'=>($user->city_id != null)?$user->city->id:0,
                ],
                'token' => $token
            ];
            return response($response, 201);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(["message"=>$validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [

            'user' => $user,
            'token' => $token,
            'message'=>'User created Successfully!'
        ];
        return response($response, 201);

    }

    public function registerAdmin(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(["message"=>$validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role'=>'admin'
        ]);

        $token = $user->createToken('my-app-token')->plainTextToken;

         $notify = Http::withToken($token)->withHeaders([
             'Content-Type' => 'application/json',
         ])->post('https://lepgo-notifications.onrender.com/api/v1/newUserCreated', [
             'userId' => $user->id,
         ]);
        $response = [

            'user' => $user,
            'token' => $token,
            'message'=>'User created Successfully!'
        ];
        return response($response, 201);

    }

    


    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response(['message'=>'You just logged out !'],200);
    }

    public function updateProfileImage(Request $request){
        $request->validate([
            'image' => 'required|mimes:jpg,jpeg,png,bmp|max:2000'
        ]);

        if($request->image){
            $file = $request->file('image');
            $uploadImage = $this->uploadFileRepository->upload($file);
        }else{
            $uploadImage = $request->user()->image;
        }

        $request->user()->profile = $uploadImage;
        $request->user()->save();

        return response([
            'message'=>'You just update profile image !',
            'image'=>(auth()->user()->profile != null )?'https://chat-broker-api.azurewebsites.net/images/'.auth()->user()->profile:null,
        ],200);

    }

    public function updateDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'name' =>['required'],
            'address' =>['required','nullable'],
            'city_id' => ['required', 'integer'],
            'phone_number' =>['required','numeric'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message'=>'Please try again !'], 400);
        }
        $request->user()->name = $request->name;
        $request->user()->address = $request->address;
        $request->user()->city_id = $request->city_id;
        $request->user()->phone_number = $request->phone_number;
        $request->user()->save();

        return response(['message'=>'You just updated your details !'],200);
    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message'=>'Please try again !'], 400);
        }
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response(['message'=>'You just Update password !'],200);

    }
    public function forgetPassword(Request $request){
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = Password::sendResetLink(
                $request->only('email')
            );
            if($status == Password::RESET_LINK_SENT){
                return response([
                    'message'=>'We just sent email to your !'
                ],200);
            }else{
                return response([
                    'message'=>'Email is not vaild !'
                ],400);
            }

    }

    public function myproducts(){
        $myproducts = Product::with(['reviews', 'sell', 'swap', 'rent'])->withCount('reviews')->where('user_id',auth()->user()->id)->get()->map(function($q){
            return [
                'id'=>$q->id,
                'title'=>$q->title,
                'desc'=>$q->desc,
                'cat_id'=>$q->category_id,
                'sell'=>$q->sell,
                'swap'=>$q->swap,
                'rent'=>$q->rent,
                'available'=>$q->available,
                'location'=>$q->location,
                'city'=>$q->city->city_name_ar . " / " .$q->city->governorate->governorate_name_ar,
                'total_rate'=>($q->reviews->count() > 0)?Review::ceil($q->reviews->sum('rate')/$q->reviews->count()):0,
                'image'=>($q->images->count()>0)?'https://chat-broker-api.azurewebsites.net/images/' .$q->images()->first()->image:null
            ];
        });
        return response()->json($myproducts);
    }
    public function getUserProfile($id){
        $user = User::where('id',$id)->firstOrfail();
        // $productsOfUser = $user->products->with()->map(function($q){
        //     return [
        //         'id'=>$q->id,
        //         'title'=>$q->title,
        //         'desc'=>$q->desc,
        //         'city'=>$q->city->city_name_ar . " / " .$q->city->governorate->governorate_name_ar,
        //         'total_rate'=>($q->reviews->count() > 0)?Review::ceil($q->reviews->sum('rate')/$q->reviews->count()):0,
        //         'image'=>($q->images->count()>0)?'https://chat-broker-api.azurewebsites.net/images/'.$q->images()->first()->image:null
        //     ];
        // });

        $myproducts = Product::with(['reviews', 'sell', 'swap', 'rent'])->withCount('reviews')->where('user_id',$id)->get()->map(function($q){
            return [
                'id'=>$q->id,
                'title'=>$q->title,
                'desc'=>$q->desc,
                'cat_id'=>$q->category_id,
                'sell'=>$q->sell,
                'swap'=>$q->swap,
                'rent'=>$q->rent,
                'available'=>$q->available,
                'location'=>$q->location,
                'city'=>$q->city->city_name_ar . " / " .$q->city->governorate->governorate_name_ar,
                'total_rate'=>($q->reviews->count() > 0)?Review::ceil($q->reviews->sum('rate')/$q->reviews->count()):0,
                'image'=>($q->images->count()>0)?'https://chat-broker-api.azurewebsites.net/images/' .$q->images()->first()->image:null
            ];
        });
        //adding badge labels
        if($totalRate=Review::where('user_id', $id)->selectRaw('SUM(rate) AS total_rate')->first()->total_rate ){

            $totalRate = ($totalRate /(5*(Review::where('user_id', $id)->selectRaw('SUM(rate) AS total_rate')->count())))*100;

            if($totalRate>= 60 && $totalRate<80){
                $badgeName='بائع جيد';
            }elseif($totalRate>= 80 && $totalRate<90 ){
                $badgeName='بائع متوسط';
            }elseif($totalRate>= 90 && $totalRate <100 ){
                $badgeName='بائع أمين';
            }else{
                $badgeName='';
            }

        }else{
            $totalRate=0;
            $badgeName='';
        }
        return response()->json([
            'user'=>[
                'id'=>$user->id,
                'name'=>$user->name,
                'phone_number'=>$user->phone_number,
                'email'=>$user->email,
                'is_verified'=>intval($user->is_verified),
                'image'=>($user->profile != null )?'https://chat-broker-api.azurewebsites.net/images/'.$user->profile:null,
                'city'=>($user->city)?$user->city->city_name_ar . " / " .$user->city->governorate->governorate_name_ar:null,
                //sum all rate of speciface user and count  number of sum and make calc
                'badge' => $totalRate,
                'badgeName' => $badgeName,
            ],
            'products'=>$myproducts
        ]);

    }

}

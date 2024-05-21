<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;

class UserController extends Controller
{
    public function userLogin(Request $request){
        $input = $request->all();
        $vallidation = Validator::make($input,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($vallidation->fails()){
            return response()->json(['error' => $vallidation->errors()],422);
        }

        if (Auth::attempt(['email' => $input['email'],'password' => $input['password']])) {
            $user  = Auth::user();
            // dd($user);

            $token = $user->createToken('MyApp')->accessToken;

            return response()->json(['token' => $token]);
        }
 

    }
    public function userDetails()
    {
        $user = Auth::guard('api')->user();

        return response()->json(['data' => $user]);
    }
}

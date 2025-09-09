<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);
        if (!Auth::attempt($cred)) {
            return response()->json(['message'=>'Invalid credentials'], 422);
        }
        $user = $request->user();
        // パーソナルアクセストークン
        $token = $user->createToken('admin')->plainTextToken;
        return response()->json(['token'=>$token]);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['ok'=>true]);
    }
}

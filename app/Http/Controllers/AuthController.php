<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Not authorized.
     */
    public function unauthorized() {
        $array = ['messages' => 'Not authorized!'];
        return response()->json($array, 401);
    }

    /**
     * Login.
     */
    public function login(Request $request) {
        $array = ['messages' => ''];

        $rules = [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $array['messages'] = $validator->getMessageBag();
        }

        $creds = $request->only(['email', 'password']);
        $token = Auth::attempt($creds);
        if (!$token) {
            $array['messages'] = 'Invalid email and/or password!';
            return $array;
        }

        $array['token'] = $token;
        $array['messages'] = 'Success!';

        return $array;
    }

    /**
     * Logout.
     */
    public function logout() {
        Auth::logout();
        return ['messages' => 'Successfully disconnected!'];
    }

    /**
     * Refresh.
     */
    public function refresh() {
        $token = Auth::refresh();

        return ['token' => $token];
    }
}

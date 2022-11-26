<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Logged user.
     */
    private $loggedUser;

    /**
     * Builder.
     */
    public function __construct() {
        $this->loggedUser = Auth::user();
    }

    /**
     * User create.
     */
    public function create(Request $request) {
        $array = ['messages' => ''];

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'birthdate' => 'required|date_format:Y-m-d',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $array['messages'] = $validator->getMessageBag();
        }

        $data = $request->only(['name', 'email', 'password', 'birthdate']);

        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->email = $data['email'];
        $newUser->password = Hash::make($data['password']);
        $newUser->birthdate = $data['birthdate'];
        $newUser->save();

        $token = Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if (!$token) {
            $array['messages'] = 'Some error occurred!';
            return $array;
        }

        $array['token'] = $token;
        $array['messages'] = 'Success!';

        return $array;
    }

    /**
     * User update.
     */
    public function update() {

    }

    /**
     * Update avatar.
     */
    public function updateAvatar() {

    }

    /**
     * Update cover.
     */
    public function updateCover() {

    }

    /**
     * Listing users.
     */
    public function read() {

    }
}

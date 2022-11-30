<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

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
    public function update(Request $request) {
        $array = ['messages' => ''];

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'string|min:6|confirmed',
            'password_confirmation' => 'string|min:6',
            'birthdate' => 'required|date_format:Y-m-d',
            'city' => 'string|max:255|nullable',
            'work' => 'string|max:255|nullable',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $array['messages'] = $validator->getMessageBag();
        }

        //dd($this->loggedUser);
        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation',
            'birthdate',
            'city',
            'work'
        ]);

        $user = User::find($this->loggedUser->id);

        if ($user) {
            $user->name = $data['name'];
            $user->birthdate = $data['birthdate'];
            $user->city = $data['city'] ?? null;
            $user->work = $data['work'] ?? null;

            // Change email if different and does not exist.
            if ($user->email !== $data['email']) {
                $validator = Validator::make($request->all(), ['email' => 'required|email|unique:users']);
                if ($validator->fails()) {
                    return $array['messages'] = $validator->getMessageBag();
                }

                $user->email = $data['email'];
            }

            // Change password if different.
            if (isset($data['password']) && !Hash::check($data['password'], $user->password)) {
                $user->password = Hash::make($data['password']);
            }
        }

        $user->save();
        $array['messages'] = 'User updated with success!';

        return $array;
    }

    /**
     * Update avatar.
     */
    public function updateAvatar(Request $request) {
        $array = ['messages' => ''];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $request->file('avatar');

        if ($image && in_array($image->getClientMimeType(), $allowedTypes)) {
            $fileName = md5(time().rand(0,9999)) . '.jpg';
            $destPath = public_path('/media/avatars');
            $img = Image::make($image->path())->fit(200, 200)->save($destPath . '/' . $fileName);

            $user = User::find($this->loggedUser->id);
            $user->avatar = $fileName;
            $user->save();

            $array['file_url'] = url('/media/avatars/' . $fileName);
            $array['messages'] = 'Avatar updated with success!';
        }
        else {
            $array['messages'] = 'File not uploaded or type of invalid file!';
            return $array;
        }

        return $array;
    }

    /**
     * Update cover.
     */
    public function updateCover(Request $request) {
        $array = ['messages' => ''];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $request->file('cover');

        if ($image && in_array($image->getClientMimeType(), $allowedTypes)) {
            $fileName = md5(time().rand(0,9999)) . '.jpg';
            $destPath = public_path('/media/covers');
            $img = Image::make($image->path())->fit(850, 310)->save($destPath . '/' . $fileName);

            $user = User::find($this->loggedUser->id);
            $user->cover = $fileName;
            $user->save();

            $array['file_url'] = url('/media/covers/' . $fileName);
            $array['messages'] = 'Cover updated with success!';
        }
        else {
            $array['messages'] = 'File not uploaded or type of invalid file!';
            return $array;
        }

        return $array;
    }

    /**
     * Listing users.
     */
    public function read() {

    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRelation;
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

        $user = Auth::user();
        $array['id'] = $user->id;
        $array['name'] = $user->name;
        $array['email'] = $user->email;
        $array['avatar'] = url('media/avatars/' . $user->avatar);
        $array['cover'] = url('media/covers/' . $user->cover);
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
    public function read($id = null) {
        $array = ['messages' => ''];
        $user = User::find($id) ?? $this->loggedUser;
        $dateFrom = new \DateTime($user->birthdate);
        $dateTo = new \DateTime('today');

        $user['avatar'] = url('media/avatars/' . $user->avatar);
        $user['cover'] = url('media/covers/' . $user->cover);
        $user['me'] = ($user->id === $this->loggedUser->id) ? true : false;
        $user['years'] = $dateFrom->diff($dateTo)->y;
        $user['following'] = $user->following()->count();
        $user['followers'] = $user->followers()->count();
        $user['photos'] = $user->posts()->where('type', 'photo')->count();
        $user['is_following'] = (bool) UserRelation::where('user_from', $this->loggedUser->id)
            ->where('user_to', $id)->count();

        $array['data'] = $user;
        return $array;
    }

    /**
     * Follow and unfollow.
     */
    public function follow($id) {
        $array = ['messages' => ''];

        if ($id == $this->loggedUser->id) {
            $array['messages'] = "You can't follow yourself.";
            return $array;
        }

        $user = User::find($id);
        if($user) {
            $relations = UserRelation::where('user_from', $this->loggedUser->id)->where('user_to', $user->id)->first();
            if (isset($relations)) {
                UserRelation::destroy($relations->id);
                $array['follow'] = false;
                $array['messages'] = 'You unfollowed.';
            }
            else {
                $newRelation = new UserRelation();
                $newRelation->user_from = $this->loggedUser->id;
                $newRelation->user_to = $id;
                $newRelation->save();
                $array['follow'] = true;
                $array['messages'] = 'You started following';
            }
        }
        else {
            $array['messages'] = "User not found.";
            return $array;
        }

        return $array;
    }

    /**
     * Get followers.
     */
    public function followers($id) {
        $array = ['messages' => ''];

        $user = User::find($id);
        if($user) {
            $followers = $user->followers;
            $array['followers'] = [];

            foreach ($followers as $relation) {
                $array['followers'][] = [
                    'id' => $relation->from->id,
                    'name' => $relation->from->name,
                    'avatar' => url('media/avatars/' . $relation->from->avatar),
                ];
            }
        }
        else {
            $array['messages'] = "User not found.";
            return $array;
        }

        return $array;
    }

    /**
     * Get following.
     */
    public function following($id) {
        $array = ['messages' => ''];

        $user = User::find($id);
        if($user) {
            $following = $user->following;
            $array['following'] = [];

            foreach ($following as $relation) {
                $array['following'][] = [
                    'id' => $relation->to->id,
                    'name' => $relation->to->name,
                    'avatar' => url('media/avatars/' . $relation->from->avatar),
                ];
            }

        }
        else {
            $array['messages'] = "User not found.";
            return $array;
        }

        return $array;
    }
}

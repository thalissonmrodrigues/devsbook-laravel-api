<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
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
     * Search.
     */
    public function search(Request $request) {
        $array = ['messages' => '', 'users' => []];
        $rules = [
            'search' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $array['messages'] = $validator->getMessageBag();
        }

        $search = $request->get('search');

        $userList = User::where('name', 'like', '%' . $search . '%')->get();
        foreach ($userList as $user) {
            $array['users'][] = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => url('media/avatars/' . $user->avatar),
            ];
        }

        return $array;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
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
     * Get likes.
     */
    public function like($id) {
        $array = ['messages' => ''];
        $post = Post::find($id);

        if (isset($post)) {
            $postIsLiked = PostLike::where('id_post', $id)->where('id_user', $this->loggedUser->id)->first();

            if (isset($postIsLiked)) {
                PostLike::destroy($postIsLiked->id);
                $array['is_liked'] = false;
                $array['messages'] = 'I liked it, it was removed.';
            }
            else {
                $newLike = new PostLike();
                $newLike->id_post = $id;
                $newLike->id_user = $this->loggedUser->id;
                $newLike->save();
                $array['is_liked'] = true;
                $array['messages'] = 'I liked it, it was added.';
            }
        }
        else {
            $array['messages'] = 'Post does not exist!';
        }

        $array['like_count'] = PostLike::where('id_post', $id)->count();
        return $array;
    }

    /**
     * Get comments.
     */
    public function comment(Request $request, $id) {
        $array = ['messages' => ''];
        $rules = [
            'body' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $array['messages'] = $validator->getMessageBag();
        }

        $body = $request->get('body');
        $post = Post::find($id);
        if (isset($post)) {
            $newComment = new PostComment();
            $newComment->id_post = $id;
            $newComment->id_user = $this->loggedUser->id;
            $newComment->body = $body;
            $newComment->save();
            $array['comment'] = $newComment;
            $array['messages'] = 'I comment it, it was added.';
        }
        else {
            $array['messages'] = 'Post does not exist!';
        }

        return $array;
    }
}

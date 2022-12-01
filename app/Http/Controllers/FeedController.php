<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class FeedController extends Controller
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
     * Get posts.
     */
    public function feed(Request $request) {
        $array = ['messages' => ''];
        $page = intval($request->get('page'));
        $perPage = 2;

        $users = [];
        $following = UserRelation::where('user_from', $this->loggedUser->id)->get();
        foreach ($following as $user) {
            $users[] = $user['user_to'];
        }
        $users[] = $this->loggedUser->id;

        $postList = Post::whereIn('id_user', $users)
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $posts = $this->postListToObject($postList, $this->loggedUser->id);

        $total = Post::whereIn('id_user', $users)->count();
        $pageCount = ceil($total / $perPage);

        $array['posts'] = $posts;
        $array['pageCount'] = $pageCount;
        $array['currentPage'] = $page;

        return $array;
    }

    /**
     * Get user posts.
     */
    public function userFeed(Request $request, $id = null) {
        $array = ['messages' => ''];
        $page = intval($request->get('page'));
        $perPage = 2;

        $user = User::find($id) ?? $this->loggedUser;
        $postList = Post::where('id_user', $user->id)
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $posts = $this->postListToObject($postList, $this->loggedUser->id);

        $total = Post::where('id_user', $user->id)->count();
        $pageCount = ceil($total / $perPage);

        $array['posts'] = $posts;
        $array['pageCount'] = $pageCount;
        $array['currentPage'] = $page;

        return $array;
    }

    /**
     * Create Posts.
     */
    public function create(Request $request) {
        $array = ['messages' => ''];

        $rules = [
            'type' => 'required|string|max:255',
            'body' => 'string|nullable',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $array['messages'] = $validator->getMessageBag();
        }

        $photo = $request->file('photo');
        $data = $request->only(['type', 'body']);

        switch($data['type']) {
            case 'text':
                if (!isset($data['body'])) {
                    $array['messages'] = "Body of the post in null!";
                    return $array;
                }
            break;

            case 'photo':
                $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

                if ($photo && in_array($photo->getClientMimeType(), $allowedTypes)) {
                    $fileName = md5(time().rand(0,9999)) . '.jpg';
                    $destPath = public_path('/media/uploads');
                    $img = Image::make($photo->path())->resize(800, null, function($constraint) {
                        $constraint->aspectRatio();
                    })->save($destPath . '/' . $fileName);

                    $data['body'] = $fileName;
                    $array['file_url'] = url('/media/uploads/' . $fileName);
                }
                else {
                    $array['messages'] = 'File not uploaded or type of invalid file!';
                    return $array;
                }
            break;

            default:
                $array['messages'] = "Invalid post type!";
                return $array;
            break;
        }

        $newPost = new Post();
        $newPost->id_user = $this->loggedUser->id;
        $newPost->type = $data['type'];
        $newPost->body = $data['body'];
        $newPost->save();

        $array['messages'] = 'Successful post!';
        return $array;
    }

    private function postListToObject($postList, $loggedId) {
        foreach ($postList as $key => $post) {
            // If the post is mine add true .
            if ($post['id_user'] == $loggedId) {
                $postList[$key]['mine'] = true;
            }
            else {
                $postList[$key]['mine'] = false;
            }

            // Fill in the user information.
            $userInfo = $post->user;
            $userInfo['avatar'] = url('media/avatars/' . $userInfo['avatar']);
            $userInfo['cover'] = url('media/covers/' . $userInfo['cover']);
            $postList[$key]['user'] = $userInfo;

            // Fill info the likes.
            $isLiked = $post->likes()->where('id_user', $loggedId)->count();
            $likes = $post->likes()->count();
            $postList[$key]['likeCount'] = $likes;
            $postList[$key]['liked'] = ($isLiked > 0) ? true : false;

            // Fill in the comments information.
            $comments = $post->comments;
            foreach ($comments as $keyComment => $comment) {
                $user = $comment->user;
                $user['avatar'] = url('media/avatars/' . $userInfo['avatar']);
                $user['cover'] = url('media/covers/' . $userInfo['cover']);
                $comments[$keyComment]['user'] = $user;
            }
            $postList[$key]['comments'] = $comments;
            $postList[$key]['commentCount'] = $post->comments()->count();
        }

        return $postList;
    }
}

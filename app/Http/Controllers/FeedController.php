<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function feed() {

    }

    /**
     * Get user posts.
     */
    public function userFeed() {

    }

    /**
     * Create Posts.
     */
    public function create() {

    }
}

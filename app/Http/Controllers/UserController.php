<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function create() {

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

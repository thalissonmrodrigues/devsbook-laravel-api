<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function search() {

    }
}

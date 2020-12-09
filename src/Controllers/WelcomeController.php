<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class WelcomeController extends BaseController
{
    public function index(Request $request) {
        return view('welcome');
    }

    public function sentry(Request $request) {
        throw new Exception('My first Sentry error!');
    }
}

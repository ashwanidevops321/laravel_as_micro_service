<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function showMessage()
    {
        return view('demo');
    }

    public function showProfile()
    {
        return view('myprofile');
    }
}


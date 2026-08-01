<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
{
    $credits = auth()->check() ? auth()->user()->credits : 0;

    return view('home', compact('credits'));
}
}
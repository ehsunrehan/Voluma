<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard',[
        'credits'=>auth()->user()->credits
        ]);
    }
    public function history()
    {
        return view('history');
    }
    public function profile()
    {
        return view('profile');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GenerationController extends Controller
{

    public function store(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
    ]);

    $image = $request->file('image');

    $filename = time().'_'.$image->getClientOriginalName();

    $path = $image->storeAs(
        'originals',
        $filename,
        'public'
    );

    return response()->json([
        'success' => true,
        'message' => 'Image uploaded successfully.',
        'image_path' => $path,
    ]);
}

public function generate(Request $request)
{

    $request->validate([

        'image_path' => 'required'

    ]);

    return response()->json([

        'success' => true,

        'step' => 'background-removal',

        'message' => 'Starting AI pipeline...'

    ]);

}

}
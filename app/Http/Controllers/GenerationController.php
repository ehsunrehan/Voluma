<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Generation;

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

    $imagePath = storage_path('app/public/' . $request->image_path);

$removeBg = Http::withHeaders([
    'X-Api-Key' => env('REMOVE_BG_API_KEY')
])
->attach(
    'image_file',
    fopen($imagePath, 'r'),
    basename($imagePath)
)
->post(
    'https://api.remove.bg/v1.0/removebg',
    [
        'size' => 'auto'
    ]
);

if (!$removeBg->successful()) {

    return response()->json([
        'success' => false,
        'message' => 'Remove.bg failed',
        'response' => $removeBg->body()
    ],500);

}

$transparentName = time().'_transparent.png';

Storage::disk('public')->put(
    'transparent/'.$transparentName,
    $removeBg->body()
);

$transparentPath = storage_path(
    'app/public/transparent/'.$transparentName
);



    $upload = Http::withToken(env('TRIPO_API_KEY'))
        ->attach(
            'file',
            fopen($transparentPath, 'r'),
            basename($transparentPath)
        )
        ->post('https://api.tripo3d.ai/v2/openapi/upload/sts');

    $uploadData = $upload->json();

    if (!isset($uploadData['data']['image_token'])) {
        return response()->json([
            'success' => false,
            'upload' => $uploadData
        ], 500);
    }

    $imageToken = $uploadData['data']['image_token'];

    $generate = Http::withToken(env('TRIPO_API_KEY'))
        ->post('https://api.tripo3d.ai/v2/openapi/task', [
            "type" => "image_to_model",
            "file" => [
                "type" => "image",
                "file_token" => $imageToken
            ]
        ]);

    $generateData = $generate->json();

    if (!isset($generateData['data']['task_id'])) {
        return response()->json([
            'success' => false,
            'generate' => $generateData
        ], 500);
    }

    $taskId = $generateData['data']['task_id'];




Generation::create([
    'user_id' => auth()->id(),
    'task_id' => $taskId,

    'original_image' => $request->image_path,

    'thumbnail' => 'transparent/'.$transparentName,

    'status' => 'processing',

    'credits_used' => 30
]);




return response()->json([
    'success' => true,
    'task_id' => $taskId,
    'generate' => $generateData
]);
}



public function checkStatus($taskId)
{
    $response = Http::withToken(env('TRIPO_API_KEY'))
        ->get("https://api.tripo3d.ai/v2/openapi/task/$taskId");

    $data = $response->json();
    if (
    isset($data['data']['status']) &&
    $data['data']['status'] === 'success'
) {

    $tripoUrl = $data['data']['output']['pbr_model'];

    Generation::where('task_id',$taskId)
        ->update([

            'status'=>'completed',

            'tripo_url'=>$tripoUrl

        ]);
}



return response()->json($data);
}

public function streamModel($taskId)
{
    set_time_limit(300);

    $generation = Generation::where('task_id', $taskId)->first();

    if (!$generation || !$generation->tripo_url) {
        abort(404);
    }

    $resource = fopen($generation->tripo_url, 'rb');

    if (!$resource) {
        abort(500);
    }

    return response()->stream(function () use ($resource) {

        while (!feof($resource)) {
            echo fread($resource, 8192);
            flush();
        }

        fclose($resource);

    }, 200, [
        'Content-Type' => 'model/gltf-binary',
        'Access-Control-Allow-Origin' => '*',
        'Cache-Control' => 'public, max-age=3600',
    ]);
}    



public function deductCredits(Request $request)
{
    $user = auth()->user();

    if ($user->credits < 10) {

        return response()->json([
            'success' => false,
            'message' => 'Not enough credits.'
        ],400);

    }

    $user->decrement('credits',10);

    return response()->json([
        'success' => true,
        'credits' => $user->fresh()->credits
    ]);
}


}
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

    if (!file_exists($imagePath)) {
        return response()->json([
            'success' => false,
            'message' => 'Image not found.'
        ], 404);
    }

    $upload = Http::withToken(env('TRIPO_API_KEY'))
        ->attach(
            'file',
            fopen($imagePath, 'r'),
            basename($imagePath)
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

    // Generation::create([
    //     'user_id' => auth()->id(),
    //     'task_id' => $taskId,
    //     'original_image' => $request->image_path,
    //     'status' => 'processing',
    // ]);

return response()->json([
    'success' => true,
    'task_id' => $taskId,
    'generate' => $generateData
]);
}



public function checkStatus($taskId)
{

    $response = Http::withToken(env('TRIPO_API_KEY'))
        ->get(
            "https://api.tripo3d.ai/v2/openapi/task/{$taskId}"
        );

    return response()->json(
        $response->json()
    );

}


public function downloadModel($taskId)
{
    set_time_limit(300);

    $response = Http::withToken(env('TRIPO_API_KEY'))
        ->get("https://api.tripo3d.ai/v2/openapi/task/{$taskId}");

    $status = $response->json();

    if (
        !isset($status['data']['result']['pbr_model']['url'])
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Model not ready.'
        ]);
    }

    $glbUrl = $status['data']['result']['pbr_model']['url'];

    // $glb = Http::get($glbUrl);
    $glb = Http::timeout(300)
    ->withOptions([
        'stream' => false,
    ])
    ->get($glbUrl);



$fileName = $taskId . '.glb';

// Storage::disk('public')->put(
//     'models/'.$fileName,
//     $glb->body()
// );


file_put_contents(
    storage_path('app/public/models/'.$fileName),
    $glb->body()
);





    return response()->json([

        'success' => true,

        'model_url' => asset('storage/models/'.$fileName)

    ]);
}


}
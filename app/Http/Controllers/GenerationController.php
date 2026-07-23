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


public function renewModel(Request $request)
{
    $request->validate([
        'task_id' => 'required'
    ]);

    $generation = Generation::where('task_id', $request->task_id)
        ->where('user_id', auth()->id())
        ->first();

    if (!$generation || !$generation->thumbnail) {
        return response()->json([
            'success' => false,
            'message' => 'Original processed image not found.'
        ], 404);
    }

    $transparentPath = storage_path('app/public/' . $generation->thumbnail);

    if (!file_exists($transparentPath)) {
        return response()->json([
            'success' => false,
            'message' => 'Processed image file missing.'
        ], 404);
    }

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

    $newTaskId = $generateData['data']['task_id'];

    Generation::create([
        'user_id' => auth()->id(),
        'task_id' => $newTaskId,
        'original_image' => $generation->original_image,
        'thumbnail' => $generation->thumbnail,
        'status' => 'processing',
        'credits_used' => 0
    ]);

    return response()->json([
        'success' => true,
        'task_id' => $newTaskId
    ]);
}

public function downloadModel(Request $request)
{
    $request->validate([
        'task_id' => 'required',
        'type' => 'required|string'
    ]);

    $taskId = $request->task_id;
    $type = strtolower($request->type);

    $generation = Generation::where('task_id', $taskId)
        ->where('user_id', auth()->id())
        ->first();

    if (!$generation || !$generation->tripo_url) {
        return response()->json([
            'success' => false,
            'message' => 'Model is not ready yet.'
        ], 404);
    }

    if ($type === 'glb') {
        return response()->json([
            'success' => true,
            'download_url' => url('/stream-model/' . $taskId)
        ]);
    }

    try {
        $convert = Http::withToken(env('TRIPO_API_KEY'))
            ->post('https://api.tripo3d.ai/v2/openapi/task', [
                "type" => "convert_model",
                "format" => strtoupper($type),
                "original_model_task_id" => $taskId
            ]);

        $convertData = $convert->json();

        if (!isset($convertData['data']['task_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'This format is not available right now. Please try GLB.'
            ], 200);
        }

        $convertTaskId = $convertData['data']['task_id'];
        $modelUrl = null;

        for ($i = 0; $i < 25; $i++) {
            sleep(2);

            $status = Http::withToken(env('TRIPO_API_KEY'))
                ->get("https://api.tripo3d.ai/v2/openapi/task/$convertTaskId");

            $statusData = $status->json();
            $currentStatus = $statusData['data']['status'] ?? null;

            if ($currentStatus === 'success') {
                $modelUrl = $statusData['data']['output']['model']
                    ?? $statusData['data']['output']['pbr_model']
                    ?? null;
                break;
            }

            if ($currentStatus === 'failed') {
                break;
            }
        }

        if (!$modelUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Conversion is taking too long. Please try again or choose GLB.'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'download_url' => $modelUrl
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'This format could not be converted. Please try GLB.'
        ], 200);
    }
}

}
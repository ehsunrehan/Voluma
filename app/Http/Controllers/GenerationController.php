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
            "model_version" => "v3.1-20260211",
            "file" => [
                "type" => "image",
                "file_token" => $imageToken
            ]
        ]);

    $generateData = $generate->json();

    if (!isset($generateData['data']['task_id'])) {
        return response()->json([
            'success' => false,
            'generate' => $generateData,
            'debug_sent_token' => $imageToken,
        ], 500);
    }

    $taskId = $generateData['data']['task_id'];




Generation::create([
    'user_id' => auth()->id(),
    'task_id' => $taskId,
    'source_type' => 'image',
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

    $fileSize = null;
    try {
        $headResponse = Http::withOptions(['allow_redirects' => true])->head($tripoUrl);
        $fileSize = $headResponse->header('Content-Length');
    } catch (\Exception $e) {
        $fileSize = null;
    }

    Generation::where('task_id',$taskId)
        ->update([

            'status'=>'completed',

            'tripo_url'=>$tripoUrl,

            'file_size'=>$fileSize

        ]);
}



return response()->json($data);
}


public function streamModel(Request $request, $taskId)
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
        'Content-Disposition' => 'attachment; filename="' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->query('name', 'model')) . '.glb"',
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


    // if (!$generation) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Original generation not found.'
    //     ], 404);
    // }

    if ($generation->source_type === 'text') {

        $generate = Http::withToken(env('TRIPO_API_KEY'))
            ->post('https://api.tripo3d.ai/v2/openapi/task', [
                "type" => "text_to_model",
                "model_version" => "v3.1-20260211",
                "prompt" => $generation->prompt
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
            'source_type' => 'text',
            'prompt' => $generation->prompt,
            'original_image' => null,
            'thumbnail' => null,
            'status' => 'processing',
            'credits_used' => 0
        ]);

        return response()->json([
            'success' => true,
            'task_id' => $newTaskId
        ]);
    }

    if (!$generation->thumbnail) {
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
            "model_version" => "v3.1-20260211",
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
        'source_type' => 'image',
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
            'download_url' => url('/stream-model/' . $taskId) . '?name=' . urlencode($request->name ?? 'model')
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


public function gallery()
{
    $cutoff = now()->subDay();

    $expired = Generation::where('user_id', auth()->id())
        ->where('created_at', '<', $cutoff)
        ->get();

    foreach ($expired as $old) {
        if ($old->original_image) {
            Storage::disk('public')->delete($old->original_image);
        }
        if ($old->thumbnail) {
            Storage::disk('public')->delete($old->thumbnail);
        }
    }

    Generation::where('user_id', auth()->id())
        ->where('created_at', '<', $cutoff)
        ->delete();

    $generations = Generation::where('user_id', auth()->id())
        ->where('status', 'completed')
        ->orderByDesc('id')
        ->get();

    $credits = auth()->user()->credits;

    return view('gallery', compact('generations', 'credits'));
}

public function generateFromText(Request $request)
{
    $request->validate([
        'prompt' => 'required|string|max:2000'
    ], [
        'prompt.max' => 'Your description is too long. Please keep it under 2000 characters.'
    ]);

    $cleanPrompt = preg_replace('/[#*_`>]/', '', $request->prompt);
    $cleanPrompt = preg_replace('/\s+/', ' ', $cleanPrompt);
    $cleanPrompt = trim($cleanPrompt);
    $cleanPrompt = mb_substr($cleanPrompt, 0, 500);

    $generate = Http::withToken(env('TRIPO_API_KEY'))
        ->post('https://api.tripo3d.ai/v2/openapi/task', [
            "type" => "text_to_model",
            "model_version" => "v3.1-20260211",
            "prompt" => $cleanPrompt
        ]);

    $generateData = $generate->json();

    if (!isset($generateData['data']['task_id'])) {
        \Log::error('Tripo text_to_model failed:', [
            'sent_prompt' => $cleanPrompt,
            'prompt_length' => mb_strlen($cleanPrompt),
            'tripo_response' => $generateData,
        ]);

        return response()->json([
            'success' => false,
            'message' => $generateData['message'] ?? 'Could not generate model. Please simplify your description and try again.',
        ], 500);
    }

    $taskId = $generateData['data']['task_id'];

    Generation::create([
        'user_id' => auth()->id(),
        'task_id' => $taskId,
        'source_type' => 'text',
        'prompt' => $cleanPrompt,
        'original_image' => null,
        'thumbnail' => null,
        'status' => 'processing',
        'credits_used' => 30
    ]);

    return response()->json([
        'success' => true,
        'task_id' => $taskId
    ]);
}


}
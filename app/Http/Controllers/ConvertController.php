<?php

namespace App\Http\Controllers;

use App\Models\Conversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class ConvertController extends Controller
{
    protected array $formats = [
        'glb'  => 'GLB (glTF Binary)',
        'gltf' => 'glTF',
        'stl'  => 'STL',
        'obj'  => 'OBJ',
        'fbx'  => 'FBX',
        'dae'  => 'DAE (Collada)',
        'ply'  => 'PLY',
        '3mf'  => '3MF',
        'usdz' => 'USDZ',
        'stp'  => 'STEP',
    ];

    public function index()
    {
        return view('convert', [
            'credits' => Auth::user()->credits ?? 0,
            'formats' => $this->formats,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB
        ], [
            'file.max' => 'File size must not exceed 100MB.',
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        if (!array_key_exists($ext, $this->formats)) {
            return response()->json([
                'success' => false,
                'invalid_format' => true,
                'message' => 'Please upload a 3D modeling/printing file (GLB, STL, OBJ, FBX, etc.)',
            ], 422);
        }

        $path = $file->store('conversions/originals', 'public');
        $hash = md5($file->getClientOriginalName() . '|' . $file->getSize());

        return response()->json([
            'success' => true,
            'path' => $path,
            'format' => $ext,
            'hash' => $hash,
            'size' => $file->getSize(),
            'name' => $file->getClientOriginalName(),
        ]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'from' => 'required|string',
            'to'   => 'required|string',
            'hash' => 'required|string',
        ]);

        $user = Auth::user();
        $cacheKey = 'convert_last_' . $user->id;
        $last = Cache::get($cacheKey);

        // same file + same target format, already converted before
        if ($last && $last['hash'] === $request->hash && $last['to'] === $request->to && $last['status'] === 'success') {
            return response()->json(['success' => true, 'already_converted' => true]);
        }

        $fullPath = storage_path('app/public/' . $request->path);

        try {
    $response = Http::withToken(config('services.convert3d.key'))
        ->timeout(300)
        ->attach('file', file_get_contents($fullPath), basename($fullPath))
        ->post('https://api.convert3d.org/convert/jobs', [
            'from' => $request->from,
            'to'   => $request->to,
        ]);
} catch (\Exception $e) {
    \Log::error('Convert3D request failed: ' . $e->getMessage());
    return response()->json([
        'success' => false,
        'message' => 'Conversion service did not respond in time. Please try a smaller file or try again.',
    ], 500);
}

\Log::info('Convert3D response:', ['status' => $response->status(), 'body' => $response->body()]);
        $job = $response->json();
        
        \Log::info('Convert3D response:', ['status' => $response->status(), 'body' => $response->body()]);


if (!isset($job['id'])) {
    \Log::error('Convert3D job creation failed:', [
        'status' => $response->status(),
        'body' => $job,
        'from' => $request->from,
        'to' => $request->to,
    ]);

    $apiMessage = $job['message'] ?? $job['error'] ?? $job['detail'] ?? null;

    return response()->json([
        'success' => false,
        'message' => $apiMessage ? ('Conversion failed: ' . $apiMessage) : 'Conversion could not start. Please try a different format or file.',
    ], 500);
}

        Conversion::create([
            'user_id' => $user->id,
            'job_id' => $job['id'],
            'original_path' => $request->path,
            'from_format' => $request->from,
            'to_format' => $request->to,
            'status' => 'queued',
            'credits_used' => 0,
        ]);

        Cache::put($cacheKey, [
            'hash' => $request->hash,
            'to' => $request->to,
            'job_id' => $job['id'],
            'status' => 'pending',
        ], now()->addHours(6));

        return response()->json(['success' => true, 'job_id' => $job['id']]);
    }

    public function checkStatus($jobId)
    {
        $response = Http::withToken(config('services.convert3d.key'))
            ->get("https://api.convert3d.org/convert/jobs/{$jobId}");

        $data = $response->json();
        $conversion = Conversion::where('job_id', $jobId)->first();

        if ($conversion && ($data['status'] ?? null) === 'success') {
    $convertedSize = null;
    try {
        if (!empty($data['signedUrl'])) {
            $headResponse = Http::timeout(15)->head($data['signedUrl']);
            $convertedSize = $headResponse->header('Content-Length');
        }
    } catch (\Exception $e) {
        \Log::warning('Could not fetch converted file size: ' . $e->getMessage());
    }

    $conversion->update([
        'status' => 'completed',
        'converted_url' => $data['signedUrl'] ?? null,
        'converted_size' => $convertedSize,
    ]);

    $data['converted_size'] = $convertedSize;
    $data['converted_name'] = 'converted-model.' . $conversion->to_format;

            $cacheKey = 'convert_last_' . $conversion->user_id;
            if ($cached = Cache::get($cacheKey)) {
                $cached['status'] = 'success';
                Cache::put($cacheKey, $cached, now()->addHours(6));
            }
        } elseif ($conversion && ($data['status'] ?? null) === 'failed') {
            $conversion->update(['status' => 'failed']);
        }

        return response()->json($data);
    }

    public function download($jobId)
    {
        $conversion = Conversion::where('job_id', $jobId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $stream = fopen($conversion->converted_url, 'rb');
        $filename = 'converted-model.' . $conversion->to_format;

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
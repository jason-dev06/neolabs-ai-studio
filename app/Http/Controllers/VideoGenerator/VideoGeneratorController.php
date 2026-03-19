<?php

namespace App\Http\Controllers\VideoGenerator;

use App\DTOs\VideoGenerationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\VideoGenerator\GenerateVideoRequest;
use App\Models\GeneratedVideo;
use App\Services\VideoGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoGeneratorController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('video-generator/index', [
            'videos' => $request->user()
                ->generatedVideos()
                ->latest()
                ->limit(20)
                ->get(),
            'credits' => $request->user()->credits,
        ]);
    }

    public function store(GenerateVideoRequest $request, VideoGeneratorService $service): RedirectResponse
    {
        $data = VideoGenerationData::fromRequest($request);
        $service->generate($data);

        return back();
    }

    public function destroy(Request $request, GeneratedVideo $generatedVideo): RedirectResponse
    {
        Gate::authorize('delete', $generatedVideo);

        $generatedVideo->delete();

        return back();
    }
}

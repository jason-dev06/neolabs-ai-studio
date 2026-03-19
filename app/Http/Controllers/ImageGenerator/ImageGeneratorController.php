<?php

namespace App\Http\Controllers\ImageGenerator;

use App\DTOs\ImageGenerationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageGenerator\GenerateImageRequest;
use App\Models\GeneratedImage;
use App\Services\ImageGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ImageGeneratorController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('image-generator/index', [
            'images' => $request->user()
                ->generatedImages()
                ->latest()
                ->limit(20)
                ->get(),
            'credits' => $request->user()->credits,
        ]);
    }

    public function store(GenerateImageRequest $request, ImageGeneratorService $service): RedirectResponse
    {
        $data = ImageGenerationData::fromRequest($request);
        $service->generate($data);

        return back();
    }

    public function destroy(Request $request, GeneratedImage $generatedImage): RedirectResponse
    {
        Gate::authorize('delete', $generatedImage);

        $generatedImage->delete();

        return back();
    }
}

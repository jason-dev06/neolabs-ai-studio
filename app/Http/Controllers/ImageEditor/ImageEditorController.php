<?php

namespace App\Http\Controllers\ImageEditor;

use App\DTOs\ImageEditor\ApplyToolData;
use App\DTOs\ImageEditor\CreateSessionData;
use App\Enums\GenerationStatus;
use App\Enums\ImageEditorTool;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageEditor\ApplyToolRequest;
use App\Http\Requests\ImageEditor\CreateSessionRequest;
use App\Models\ImageEditSession;
use App\Services\ImageEditor\ImageEditorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ImageEditorController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('image-editor/index', [
            'sessions' => $request->user()
                ->imageEditSessions()
                ->with('steps')
                ->latest()
                ->limit(20)
                ->get(),
            'generatedImages' => $request->user()
                ->generatedImages()
                ->where('status', GenerationStatus::Completed)
                ->latest()
                ->limit(20)
                ->get(),
            'credits' => $request->user()->credits,
            'tools' => collect(ImageEditorTool::cases())->map(fn (ImageEditorTool $tool) => [
                'value' => $tool->value,
                'label' => $tool->label(),
                'description' => $tool->description(),
                'creditCost' => $tool->creditCost(),
            ])->values()->all(),
        ]);
    }

    public function show(Request $request, ImageEditSession $session): Response
    {
        Gate::authorize('view', $session);

        $session->load('steps');

        return Inertia::render('image-editor/show', [
            'session' => $session,
            'currentImageUrl' => $session->currentImageUrl(),
            'credits' => $request->user()->credits,
            'tools' => collect(ImageEditorTool::cases())->map(fn (ImageEditorTool $tool) => [
                'value' => $tool->value,
                'label' => $tool->label(),
                'description' => $tool->description(),
                'creditCost' => $tool->creditCost(),
            ])->values()->all(),
        ]);
    }

    public function store(CreateSessionRequest $request, ImageEditorService $service): RedirectResponse
    {
        $data = CreateSessionData::fromRequest($request);
        $session = $service->createSession($data);

        return redirect()->route('image-editor.show', $session);
    }

    public function applyTool(ApplyToolRequest $request, ImageEditSession $session, ImageEditorService $service): RedirectResponse
    {
        Gate::authorize('update', $session);

        if ($session->hasActiveStep()) {
            return back()->withErrors(['tool' => 'An edit is already in progress. Please wait for it to complete.']);
        }

        $data = ApplyToolData::fromRequest($request, $session->id);
        $service->applyTool($data);

        return back();
    }

    public function undo(Request $request, ImageEditSession $session, ImageEditorService $service): RedirectResponse
    {
        Gate::authorize('update', $session);

        $service->undo($session);

        return back();
    }

    public function redo(Request $request, ImageEditSession $session, ImageEditorService $service): RedirectResponse
    {
        Gate::authorize('update', $session);

        $service->redo($session);

        return back();
    }

    public function destroy(Request $request, ImageEditSession $session, ImageEditorService $service): RedirectResponse
    {
        Gate::authorize('delete', $session);

        $service->deleteSession($session);

        return redirect()->route('image-editor.index');
    }
}

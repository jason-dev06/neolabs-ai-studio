<?php

namespace App\Http\Controllers\VideoEditor;

use App\DTOs\VideoEditor\ApplyToolData;
use App\DTOs\VideoEditor\CreateSessionData;
use App\Enums\GenerationStatus;
use App\Enums\VideoEditorTool;
use App\Http\Controllers\Controller;
use App\Http\Requests\VideoEditor\ApplyToolRequest;
use App\Http\Requests\VideoEditor\CreateSessionRequest;
use App\Models\VideoEditSession;
use App\Services\VideoEditor\VideoEditorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoEditorController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('video-editor/index', [
            'sessions' => $request->user()
                ->videoEditSessions()
                ->with('steps')
                ->latest()
                ->limit(20)
                ->get(),
            'generatedVideos' => $request->user()
                ->generatedVideos()
                ->where('status', GenerationStatus::Completed)
                ->latest()
                ->limit(20)
                ->get(),
            'credits' => $request->user()->credits,
            'tools' => collect(VideoEditorTool::cases())->map(fn (VideoEditorTool $tool) => [
                'value' => $tool->value,
                'label' => $tool->label(),
                'description' => $tool->description(),
                'creditCost' => $tool->creditCost(),
            ])->values()->all(),
        ]);
    }

    public function show(Request $request, VideoEditSession $session): Response
    {
        Gate::authorize('view', $session);

        $session->load('steps');

        return Inertia::render('video-editor/show', [
            'session' => $session,
            'currentVideoUrl' => $session->currentVideoUrl(),
            'credits' => $request->user()->credits,
            'tools' => collect(VideoEditorTool::cases())->map(fn (VideoEditorTool $tool) => [
                'value' => $tool->value,
                'label' => $tool->label(),
                'description' => $tool->description(),
                'creditCost' => $tool->creditCost(),
            ])->values()->all(),
        ]);
    }

    public function store(CreateSessionRequest $request, VideoEditorService $service): RedirectResponse
    {
        $data = CreateSessionData::fromRequest($request);
        $session = $service->createSession($data);

        return redirect()->route('video-editor.show', $session);
    }

    public function applyTool(ApplyToolRequest $request, VideoEditSession $session, VideoEditorService $service): RedirectResponse
    {
        Gate::authorize('update', $session);

        if ($session->hasActiveStep()) {
            return back()->withErrors(['tool' => 'An edit is already in progress. Please wait for it to complete.']);
        }

        $data = ApplyToolData::fromRequest($request, $session->id);
        $service->applyTool($data);

        return back();
    }

    public function undo(Request $request, VideoEditSession $session, VideoEditorService $service): RedirectResponse
    {
        Gate::authorize('update', $session);

        $service->undo($session);

        return back();
    }

    public function redo(Request $request, VideoEditSession $session, VideoEditorService $service): RedirectResponse
    {
        Gate::authorize('update', $session);

        $service->redo($session);

        return back();
    }

    public function destroy(Request $request, VideoEditSession $session, VideoEditorService $service): RedirectResponse
    {
        Gate::authorize('delete', $session);

        $service->deleteSession($session);

        return redirect()->route('video-editor.index');
    }
}

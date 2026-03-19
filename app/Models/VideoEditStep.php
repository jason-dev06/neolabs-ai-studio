<?php

namespace App\Models;

use App\Enums\GenerationStatus;
use App\Enums\VideoEditorTool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoEditStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'step_number',
        'tool',
        'tool_settings',
        'credit_cost',
        'status',
        'file_path',
        'file_url',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'tool' => VideoEditorTool::class,
            'status' => GenerationStatus::class,
            'tool_settings' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(VideoEditSession::class, 'session_id');
    }
}

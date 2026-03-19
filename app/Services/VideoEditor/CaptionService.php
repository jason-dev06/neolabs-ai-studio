<?php

namespace App\Services\VideoEditor;

use Illuminate\Support\Facades\Process;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Audio;
use RuntimeException;

class CaptionService
{
    /**
     * Generate SRT subtitle content from a video file.
     *
     * Extracts audio, transcribes it using Prism with OpenAI Whisper,
     * requesting SRT format directly from the API.
     */
    public function generateSrt(string $videoPath, string $language = 'en'): string
    {
        $audioPath = $this->extractAudio($videoPath);

        try {
            $response = Prism::audio()
                ->using('openai', 'whisper-1')
                ->withClientOptions(['timeout' => 120])
                ->withInput(Audio::fromLocalPath($audioPath, 'audio/mpeg'))
                ->withProviderOptions([
                    'language' => $language,
                    'response_format' => 'srt',
                ])
                ->asText();

            $srt = trim($response->text);

            if (empty($srt)) {
                throw new RuntimeException('Transcription returned empty SRT.');
            }

            return $srt;
        } finally {
            @unlink($audioPath);
        }
    }

    /**
     * Extract audio from video file using FFmpeg.
     */
    private function extractAudio(string $videoPath): string
    {
        $audioPath = sys_get_temp_dir().'/'.uniqid('caption_audio_').'.mp3';

        $result = Process::timeout(120)->run([
            'ffmpeg', '-y',
            '-i', $videoPath,
            '-vn',
            '-acodec', 'libmp3lame',
            '-q:a', '4',
            $audioPath,
        ]);

        if ($result->failed()) {
            throw new RuntimeException("Audio extraction failed: {$result->errorOutput()}");
        }

        if (! file_exists($audioPath)) {
            throw new RuntimeException('FFmpeg did not produce an audio file.');
        }

        return $audioPath;
    }

}

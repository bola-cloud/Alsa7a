<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessStoryVideo implements ShouldQueue
{
    use Queueable;

    protected $story;
    protected $rawPath;

    /**
     * Create a new job instance.
     */
    public function __construct(\App\Models\Story $story, $rawPath)
    {
        $this->story = $story;
        $this->rawPath = $rawPath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($this->rawPath)) {
            return;
        }

        try {
            $hlsFolder = 'stories/hls/' . $this->story->id;
            $m3u8Path = $hlsFolder . '/playlist.m3u8';
            
            $bitrate = (new \FFMpeg\Format\Video\X264('aac', 'libx264'))
                        ->setKiloBitrate(2000)
                        ->setAdditionalParameters(['-preset', 'fast']);

            \ProtoneMedia\LaravelFFMpeg\Support\FFMpeg::fromDisk('public')
                ->open($this->rawPath)
                ->exportForHLS()
                ->setSegmentLength(3) // 3 second segments for fast playback
                ->setKeyFrameInterval(48) // Assuming ~24fps, keyframe every 2 seconds
                ->toDisk('public')
                ->addFormat($bitrate)
                ->save($m3u8Path);

            // Update Story with new HLS path
            $this->story->update([
                'media_path' => $m3u8Path
            ]);

            // Delete raw MP4
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->rawPath);

        } catch (\Exception $e) {
            \Log::error('Story HLS Processing Failed for Story ID ' . $this->story->id . ': ' . $e->getMessage());
        }
    }
}

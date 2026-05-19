<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\CommunityPost;

class ProcessReelVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $post;
    public $type;
    public $timeout = 3600; // Allow 1 hour for video processing

    /**
     * Create a new job instance.
     */
    public function __construct($post, $type = 'post')
    {
        $this->post = $post;
        $this->type = $type; // 'post' or 'community'
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->post->update(['processing_status' => 'processing']);

            // Raw MP4 path (stored in 'image' temporarily during upload)
            $rawPath = $this->post->getRawOriginal('image');
            
            if (!$rawPath || !Storage::disk('public')->exists($rawPath)) {
                $this->post->update(['processing_status' => 'failed']);
                return;
            }

            // Create HLS format with optimized encoding speed (superfast preset)
            $lowBitrate = (new X264('aac', 'libx264'))->setKiloBitrate(500)
                ->setAdditionalParameters(['-preset', 'superfast']);
                
            $midBitrate = (new X264('aac', 'libx264'))->setKiloBitrate(1500)
                ->setAdditionalParameters(['-preset', 'superfast']);
                
            $highBitrate = (new X264('aac', 'libx264'))->setKiloBitrate(3000)
                ->setAdditionalParameters(['-preset', 'superfast']);

            $randomHash = Str::random(10);
            $outputDir = 'reels/' . $this->post->id . '_' . $randomHash;
            $hlsPath = $outputDir . '/playlist.m3u8';

            FFMpeg::fromDisk('public')
                ->open($rawPath)
                ->exportForHLS()
                ->addFormat($lowBitrate)
                ->addFormat($midBitrate)
                ->addFormat($highBitrate)
                ->onProgress(function ($percentage) {
                    // Could broadcast progress via Reverb/WebSockets here if needed
                })
                ->toDisk('public')
                ->save($hlsPath);

            // Once successful, update the DB with HLS path and mark as completed
            $this->post->update([
                'hls_url' => $hlsPath,
                'processing_status' => 'completed',
                // Keep original image as thumbnail if the client uploaded one, or generate one if needed
            ]);

            // Optional: Delete the raw MP4 to save space
            Storage::disk('public')->delete($rawPath);
            
            // Note: Since we deleted the original mp4, the 'image' field now points to a non-existent file
            // Let's set 'image' to null if they didn't upload a thumbnail, or leave it if 'video_thumbnail' is used.
            // In Alsa7a, mobile passes 'video_thumbnail'.
            $this->post->update(['image' => null]); 

        } catch (\Exception $e) {
            \Log::error('Reel Video Processing Failed: ' . $e->getMessage());
            $this->post->update(['processing_status' => 'failed']);
        }
    }
}

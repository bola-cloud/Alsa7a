<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete stories that have expired to free up storage space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredStories = \App\Models\Story::where('expires_at', '<=', now())->get();

        $count = 0;
        foreach ($expiredStories as $story) {
            // Delete associated media file if it exists
            if ($story->media_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($story->media_path);
            }
            // Delete story from DB (will cascade delete views)
            $story->delete();
            $count++;
        }

        $this->info("Successfully cleaned up {$count} expired stories.");
    }
}

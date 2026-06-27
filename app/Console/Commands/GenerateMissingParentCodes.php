<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Str;

class GenerateMissingParentCodes extends Command
{
    protected $signature = 'users:generate-parent-codes';
    protected $description = 'Generate parent_code for all users who do not have one yet';

    public function handle()
    {
        $users = User::whereNull('parent_code')->get();
        $count = 0;

        foreach ($users as $user) {
            do {
                $code = 'P-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            } while (User::where('parent_code', $code)->exists());

            $user->parent_code = $code;
            $user->save();
            $count++;
        }

        $this->info("Done! Generated parent codes for {$count} users.");
        return 0;
    }
}

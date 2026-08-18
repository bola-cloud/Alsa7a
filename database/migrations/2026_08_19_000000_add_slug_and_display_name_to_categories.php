<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Splits a category's identity from the text people read.
 *
 * `name` / `name_en` / `name_ar` currently do both jobs: nine places across the
 * backend and the app decide "is this the club category?" by comparing those
 * strings, so re-wording a category for users would silently break permissions.
 *
 * `slug` becomes the identity — set once, never shown, never edited.
 * `display_name_*` becomes the wording — free to change, read by nobody but the
 * UI. The three existing name columns are left exactly as they are so current
 * app builds keep matching on them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('display_name_en')->nullable()->after('name_ar');
            $table->string('display_name_ar')->nullable()->after('display_name_en');
        });

        $this->backfillSlugs();
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'display_name_en', 'display_name_ar']);
        });
    }

    /**
     * Every category gets a slug derived from its English name. The two the
     * code actually keys off are pinned by id, so they cannot drift even if
     * someone re-words them from the panel later.
     */
    protected function backfillSlugs(): void
    {
        $pinned = [
            19 => 'club',
            13 => 'football_player',
        ];

        $used = [];

        foreach (DB::table('categories')->orderBy('id')->get() as $category) {
            $slug = $pinned[$category->id] ?? Str::slug($category->name_en ?: $category->name ?: "category-{$category->id}", '_');

            if ($slug === '' || in_array($slug, $used, true)) {
                $slug = trim($slug . '_' . $category->id, '_');
            }

            $used[] = $slug;

            DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
        }
    }
};

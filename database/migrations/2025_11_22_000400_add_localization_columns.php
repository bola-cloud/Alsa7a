<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // categories
        if (! Schema::hasColumn('categories', 'name_en')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name');
                $table->string('name_ar')->nullable()->after('name_en');
                $table->text('description_en')->nullable()->after('description');
                $table->text('description_ar')->nullable()->after('description_en');
            });
        }

        // sports
        if (! Schema::hasColumn('sports', 'name_en')) {
            Schema::table('sports', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name');
                $table->string('name_ar')->nullable()->after('name_en');
                $table->text('description_en')->nullable()->after('description');
                $table->text('description_ar')->nullable()->after('description_en');
            });
        }

        // clubs
        if (! Schema::hasColumn('clubs', 'name_en')) {
            Schema::table('clubs', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name');
                $table->string('name_ar')->nullable()->after('name_en');
                $table->text('description_en')->nullable()->after('description');
                $table->text('description_ar')->nullable()->after('description_en');
            });
        }

        // events
        if (! Schema::hasColumn('events', 'title_en')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('title_en')->nullable()->after('title');
                $table->string('title_ar')->nullable()->after('title_en');
                $table->text('description_en')->nullable()->after('description');
                $table->text('description_ar')->nullable()->after('description_en');
            });
        }

        // sliders
        if (! Schema::hasColumn('sliders', 'title_en')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->string('title_en')->nullable()->after('title');
                $table->string('title_ar')->nullable()->after('title_en');
            });
        }

        // questions
        if (! Schema::hasColumn('questions', 'question_en')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->text('question_en')->nullable()->after('question');
                $table->text('question_ar')->nullable()->after('question_en');
                $table->json('choices_en')->nullable()->after('choices');
                $table->json('choices_ar')->nullable()->after('choices_en');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('categories', 'name_en')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn(['name_en','name_ar','description_en','description_ar']);
            });
        }

        if (Schema::hasColumn('sports', 'name_en')) {
            Schema::table('sports', function (Blueprint $table) {
                $table->dropColumn(['name_en','name_ar','description_en','description_ar']);
            });
        }

        if (Schema::hasColumn('clubs', 'name_en')) {
            Schema::table('clubs', function (Blueprint $table) {
                $table->dropColumn(['name_en','name_ar','description_en','description_ar']);
            });
        }

        if (Schema::hasColumn('events', 'title_en')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn(['title_en','title_ar','description_en','description_ar']);
            });
        }

        if (Schema::hasColumn('sliders', 'title_en')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->dropColumn(['title_en','title_ar']);
            });
        }

        if (Schema::hasColumn('questions', 'question_en')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn(['question_en','question_ar','choices_en','choices_ar']);
            });
        }
    }
};

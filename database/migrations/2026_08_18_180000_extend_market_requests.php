<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Requests ("الطلبات") get the details an applicant needs before deciding:
     * where it is, when it is, and what it will cost them if accepted — plus
     * publisher-defined questions they answer while applying.
     *
     * Every new column is nullable: all of this is optional and display-only.
     */
    public function up(): void
    {
        Schema::table('market_requests', function (Blueprint $table) {
            // Map pin — same shape as user_events, 10,7 covers the full range.
            $table->decimal('latitude', 10, 7)->nullable()->after('description');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            // Written location, e.g. "ملعب النادي الأهلي، مسقط".
            $table->string('address')->nullable()->after('longitude');
            // When the request takes place (a match, a training session…).
            $table->dateTime('scheduled_at')->nullable()->after('address');
            // What an accepted applicant is expected to pay (pitch booking
            // share, entry fee…). Display-only — no payment is processed.
            $table->decimal('cost', 10, 3)->nullable()->after('scheduled_at');
        });

        // Questions the publisher attaches to their request.
        Schema::create('market_request_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_request_id')->constrained('market_requests')->cascadeOnDelete();
            $table->string('question');
            $table->boolean('is_required')->default(false);
            // List of choices the applicant picks from. NULL = free text answer.
            $table->json('options')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['market_request_id', 'sort_order']);
        });

        // What each applicant answered.
        Schema::create('market_application_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_application_id')->constrained('market_applications')->cascadeOnDelete();
            $table->foreignId('market_request_question_id')->constrained('market_request_questions')->cascadeOnDelete();
            $table->text('answer')->nullable();
            $table->timestamps();

            // One answer per question per application.
            $table->unique(['market_application_id', 'market_request_question_id'], 'market_answer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_application_answers');
        Schema::dropIfExists('market_request_questions');

        Schema::table('market_requests', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address', 'scheduled_at', 'cost']);
        });
    }
};
